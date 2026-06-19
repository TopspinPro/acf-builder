const CONFIG_NAME = "tsp_acf_field_group_switcher"
const CHANGE_EVENT = "tsp-acf-field-group-switcher:change"
const ALL_FIELDS_MODE = "__all"
const FIELD_INPUT_SELECTOR = "select, input, textarea"
const FIELD_VISIBILITY_SELECTOR = "[data-tsp-acf-visible-if]"
const VISIBILITY_HIDDEN_CLASS = "tsp-acf-visibility-hidden"
const $ = window.jQuery ?? window.$

const onReady = (callback) => {
  if (document.readyState !== "complete") {
    window.addEventListener("load", callback, { once: true })
    return
  }

  callback()
}

const escapeSelectorValue = (value) => {
  if (window.CSS?.escape) {
    return window.CSS.escape(value)
  }

  return value.replace(/"/g, '\\"')
}

const findPostbox = (groupKey) => {
  if (!groupKey) {
    return null
  }

  const byId = document.getElementById(`acf-${groupKey}`)
  if (byId) {
    return byId.closest(".postbox") ?? byId
  }

  const byDataKey = document.querySelector(`[data-key="${escapeSelectorValue(groupKey)}"]`)

  return byDataKey?.closest(".postbox") ?? byDataKey ?? null
}

const sortByDocumentPosition = (items) => {
  return [...items].sort((a, b) => {
    if (a === b) {
      return 0
    }

    return a.compareDocumentPosition(b) & Node.DOCUMENT_POSITION_PRECEDING ? 1 : -1
  })
}

const sortGroups = (groups) =>
  [...groups].sort((left, right) => {
    const orderComparison = Number(right.order ?? 10) - Number(left.order ?? 10)
    if (orderComparison !== 0) {
      return orderComparison
    }

    const labelComparison = String(left.label ?? "").localeCompare(String(right.label ?? ""), undefined, {
      sensitivity: "base",
    })

    return labelComparison !== 0 ? labelComparison : String(left.key).localeCompare(String(right.key))
  })

const isSwitcherGroup = (group) => group.switcherEnabled !== false

const findVisibilityField = (condition) => {
  if (!condition) {
    return null
  }

  if (condition.fieldKey) {
    const byKey = document.querySelector(
      `.acf-field[data-key="${escapeSelectorValue(condition.fieldKey)}"]`
    )

    if (byKey) {
      return byKey
    }
  }

  if (condition.fieldName) {
    return document.querySelector(
      `.acf-field[data-name="${escapeSelectorValue(condition.fieldName)}"]`
    )
  }

  return null
}

const getFieldValue = (field) => {
  const select = field.querySelector("select")
  if (select) {
    return select.value
  }

  const checkedInput = field.querySelector(
    "input[type='radio']:checked, input[type='checkbox']:checked"
  )
  if (checkedInput) {
    return checkedInput.value
  }

  const input = field.querySelector("input:not([type='hidden']), textarea")
  if (input) {
    return input.value
  }

  return field.querySelector("input[type='hidden']")?.value ?? ""
}

const evaluateVisibilityCondition = (condition) => {
  if (!condition) {
    return true
  }

  const field = findVisibilityField(condition)
  if (!field) {
    return true
  }

  const fieldValue = String(getFieldValue(field))
  const expectedValue = String(condition.value ?? "")
  const matches = fieldValue === expectedValue

  return condition.operator === "!=" ? !matches : matches
}

const parseVisibilityCondition = (value) => {
  if (!value) {
    return null
  }

  try {
    const parsed = JSON.parse(value)

    return parsed && typeof parsed === "object" ? parsed : null
  } catch {
    return null
  }
}

const getFieldVisibilityTargets = () =>
  Array.from(document.querySelectorAll(FIELD_VISIBILITY_SELECTOR))
    .map((target) => ({
      condition: parseVisibilityCondition(target.dataset.tspAcfVisibleIf),
      target,
    }))
    .filter((item) => item.condition)

const getVisibilityConditions = (groups) => [
  ...groups.map((group) => group.visibleIf).filter(Boolean),
  ...getFieldVisibilityTargets().map((item) => item.condition),
]

const isVisibilityControllerTarget = (target, conditions) => {
  const field = target instanceof Element ? target.closest(".acf-field") : null
  if (!field) {
    return false
  }

  return conditions.some((condition) => {
    return Boolean(
      condition &&
        ((condition.fieldKey && field.dataset.key === condition.fieldKey) ||
          (condition.fieldName && field.dataset.name === condition.fieldName))
    )
  })
}

const getVisibilityControllerSelector = (conditions) => {
  const selectors = conditions
    .flatMap((condition) => [
      condition?.fieldKey
        ? `.acf-field[data-key="${escapeSelectorValue(condition.fieldKey)}"]`
        : "",
      condition?.fieldName
        ? `.acf-field[data-name="${escapeSelectorValue(condition.fieldName)}"]`
        : "",
    ])
    .filter(Boolean)

  return [...new Set(selectors)]
    .flatMap((selector) =>
      FIELD_INPUT_SELECTOR.split(", ").map((inputSelector) => `${selector} ${inputSelector}`)
    )
    .join(", ")
}

const getAvailableGroupKeys = (groups) => {
  return new Set(
    groups
      .filter((group) => evaluateVisibilityCondition(group.visibleIf))
      .map((group) => group.key)
  )
}

const syncFieldVisibilityTargets = () => {
  getFieldVisibilityTargets().forEach(({ condition, target }) => {
    const isVisible = evaluateVisibilityCondition(condition)

    target.hidden = !isVisible
    target.classList.toggle(VISIBILITY_HIDDEN_CLASS, !isVisible)
  })
}

const isModeAvailable = (mode, availableGroupKeys) => {
  if (mode.key === ALL_FIELDS_MODE) {
    return mode.groups.filter((group) => availableGroupKeys.has(group)).length > 1
  }

  return mode.groups.every((group) => availableGroupKeys.has(group))
}

const getFallbackMode = (modes, availableGroupKeys, preferredFallback) => {
  if (
    preferredFallback &&
    modes.some(
      (mode) => mode.key === preferredFallback && isModeAvailable(mode, availableGroupKeys)
    )
  ) {
    return preferredFallback
  }

  return (
    modes.find((mode) => mode.key !== ALL_FIELDS_MODE && isModeAvailable(mode, availableGroupKeys))
      ?.key ??
    modes.find((mode) => isModeAvailable(mode, availableGroupKeys))?.key ??
    preferredFallback
  )
}

const normalizeMode = (mode, modes, fallback, availableGroupKeys) => {
  return modes.some(
    (item) => item.key === mode && isModeAvailable(item, availableGroupKeys)
  )
    ? mode
    : fallback
}

const getStoredMode = (storageKey) => {
  try {
    return window.localStorage.getItem(storageKey)
  } catch {
    return null
  }
}

const setStoredMode = (storageKey, mode) => {
  try {
    window.localStorage.setItem(storageKey, mode)
  } catch {
    // Local storage can be unavailable in locked-down browser contexts.
  }
}

const getStorageKey = (config, groups) => {
  const screenId = config.screenId || document.body?.className || "acf"
  const groupKeys = groups.map((group) => group.key).join("|")

  return `${config.storagePrefix || CONFIG_NAME}:${screenId}:${groupKeys}`
}

const buildModes = (groups, allFieldsLabel) => [
  ...groups.map((group) => ({
    key: group.key,
    label: group.label,
    groups: [group.key],
  })),
  {
    key: ALL_FIELDS_MODE,
    label: allFieldsLabel || "All fields",
    groups: groups.map((group) => group.key),
  },
]

const buildSwitcher = (config, modes) => {
  const root = document.createElement("div")
  root.className = "tsp-acf-field-group-switcher"
  root.setAttribute("role", "group")
  root.setAttribute("aria-label", config.ariaLabel || "ACF field group editor view")

  const label = document.createElement("span")
  label.className = "tsp-acf-field-group-switcher__label"
  label.textContent = config.editLabel || "Edit"
  root.append(label)

  const buttons = new Map()

  modes.forEach((mode) => {
    const button = document.createElement("button")
    button.type = "button"
    button.className = "button tsp-acf-field-group-switcher__button"
    button.dataset.mode = mode.key
    button.textContent = mode.label

    root.append(button)
    buttons.set(mode.key, button)
  })

  return { buttons, root }
}

const serializeMode = (mode) => ({
  key: mode.key,
  label: mode.label,
  groups: [...mode.groups],
})

const dispatchModeChange = (
  modeKey,
  mode,
  availableModes,
  availableGroupKeys,
  visibleGroups
) => {
  document.dispatchEvent(
    new CustomEvent(CHANGE_EVENT, {
      detail: {
        activeMode: modeKey,
        activeLabel: mode?.label ?? "",
        visibleGroups: [...visibleGroups],
        availableGroups: [...availableGroupKeys],
        availableModes: availableModes.map(serializeMode),
        allFieldsMode: ALL_FIELDS_MODE,
      },
    })
  )
}

const applyMode = (
  modeKey,
  modes,
  postboxes,
  buttons,
  root,
  availableGroupKeys,
  switcherGroupKeys
) => {
  const mode = modes.find((item) => item.key === modeKey)
  const availableModes = modes.filter((item) => isModeAvailable(item, availableGroupKeys))
  const visibleGroups = new Set(
    (mode?.groups ?? []).filter((group) => availableGroupKeys.has(group))
  )

  Object.entries(postboxes).forEach(([group, postbox]) => {
    const isSwitcherManaged = switcherGroupKeys.has(group)
    const isVisible =
      availableGroupKeys.has(group) && (!isSwitcherManaged || visibleGroups.has(group))

    postbox.hidden = !isVisible
    postbox.classList.toggle("tsp-acf-field-group-switcher-hidden", !isVisible)
  })

  if (!root) {
    return
  }

  buttons.forEach((button, key) => {
    const buttonMode = modes.find((item) => item.key === key)
    const isAvailable = Boolean(
      buttonMode && isModeAvailable(buttonMode, availableGroupKeys)
    )
    const isActive = key === modeKey

    button.hidden = !isAvailable
    button.disabled = !isAvailable
    button.classList.toggle("tsp-acf-field-group-switcher-hidden", !isAvailable)
    button.classList.toggle("button-primary", isActive)
    button.setAttribute("aria-hidden", isAvailable ? "false" : "true")
    button.setAttribute("aria-pressed", isActive ? "true" : "false")
  })

  root.hidden = availableModes.length < 2
  root.classList.toggle("tsp-acf-field-group-switcher-hidden", availableModes.length < 2)
  root.dataset.mode = modeKey
  dispatchModeChange(modeKey, mode, availableModes, availableGroupKeys, visibleGroups)
}

export default function initAcfFieldGroupSwitcher() {
  const config = window[CONFIG_NAME]
  const configuredGroups = Array.isArray(config?.groups) ? config.groups : []
  if (configuredGroups.length < 1) {
    return
  }

  onReady(() => {
    const presentGroups = sortGroups(
      configuredGroups
        .map((group) => ({
          ...group,
          postbox: findPostbox(group.key),
        }))
        .filter((group) => group.postbox)
    )

    if (presentGroups.length < 1) {
      return
    }

    const switcherGroups = presentGroups.filter(isSwitcherGroup)
    const shouldRenderSwitcher = switcherGroups.length >= 2
    const switcherGroupKeys = new Set(
      shouldRenderSwitcher ? switcherGroups.map((group) => group.key) : []
    )
    const postboxes = Object.fromEntries(presentGroups.map((group) => [group.key, group.postbox]))

    Object.values(postboxes).forEach((postbox) => {
      postbox.classList.remove("hide-if-js")
    })

    const modes = shouldRenderSwitcher ? buildModes(switcherGroups, config.allFieldsLabel) : []
    const preferredFallbackMode = switcherGroups[0]?.key
    let buttons = new Map()
    let root = null

    if (shouldRenderSwitcher) {
      const switcher = buildSwitcher(config, modes)
      buttons = switcher.buttons
      root = switcher.root
      const firstPostbox = sortByDocumentPosition(Object.values(postboxes))[0]

      firstPostbox.insertAdjacentElement("beforebegin", root)
    }

    const storageKey = getStorageKey(config, switcherGroups)
    let activeMode = getStoredMode(storageKey) ?? preferredFallbackMode
    const syncVisibility = () => {
      const availableGroupKeys = getAvailableGroupKeys(presentGroups)
      const fallbackMode = getFallbackMode(modes, availableGroupKeys, preferredFallbackMode)

      activeMode = normalizeMode(activeMode, modes, fallbackMode, availableGroupKeys)
      applyMode(
        activeMode,
        modes,
        postboxes,
        buttons,
        root,
        availableGroupKeys,
        switcherGroupKeys
      )
      syncFieldVisibilityTargets()
    }

    syncVisibility()

    const visibilityControllerSelector = getVisibilityControllerSelector(
      getVisibilityConditions(presentGroups)
    )
    if (visibilityControllerSelector && $) {
      $(document).on("change", visibilityControllerSelector, syncVisibility)
    }

    buttons.forEach((button, mode) => {
      button.addEventListener("click", () => {
        const availableGroupKeys = getAvailableGroupKeys(presentGroups)
        const fallbackMode = getFallbackMode(modes, availableGroupKeys, preferredFallbackMode)

        activeMode = normalizeMode(mode, modes, fallbackMode, availableGroupKeys)

        setStoredMode(storageKey, activeMode)
        applyMode(
          activeMode,
          modes,
          postboxes,
          buttons,
          root,
          availableGroupKeys,
          switcherGroupKeys
        )
      })
    })

    document.addEventListener("change", (event) => {
      if (isVisibilityControllerTarget(event.target, getVisibilityConditions(presentGroups))) {
        syncVisibility()
      }
    })

    window.acf?.addAction?.("append", syncVisibility)
    window.acf?.addAction?.("ready", syncVisibility)
  })
}
