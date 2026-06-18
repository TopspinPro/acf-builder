const CONFIG_NAME = "tsp_acf_tab_preferences"

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

const isSkippedByAcfPreference = (tabWrap) => {
  const hasFieldSettingsTabs = Array.from(tabWrap.children).some((child) =>
    child.classList.contains("acf-field-settings-tab-bar")
  )

  return hasFieldSettingsTabs || tabWrap.closest("#acf-advanced-settings.postbox")
}

const getPreferenceTabWraps = () =>
  Array.from(document.querySelectorAll(".acf-tab-wrap")).filter(
    (tabWrap) => !isSkippedByAcfPreference(tabWrap)
  )

const getAcfTabIndex = (tabWrap) => {
  const $ = window.jQuery ?? window.$
  const instance = $ && window.acf?.getInstance ? window.acf.getInstance($(tabWrap)) : null
  const acfIndex = Number(instance?.get?.("index"))

  if (Number.isInteger(acfIndex) && acfIndex >= 0) {
    return acfIndex
  }

  const fallbackIndex = getPreferenceTabWraps().indexOf(tabWrap)

  return fallbackIndex >= 0 ? fallbackIndex : null
}

const getResettableTabIndexes = (groups) => {
  const indexes = new Set()

  groups.forEach((group) => {
    const postbox = findPostbox(group.key)

    if (!postbox) {
      return
    }

    postbox.querySelectorAll(".acf-tab-wrap").forEach((tabWrap) => {
      const index = getAcfTabIndex(tabWrap)

      if (index !== null) {
        indexes.add(index)
      }
    })
  })

  return [...indexes]
}

const getStoredTabs = () => {
  const tabs = window.acf?.getPreference?.("this.tabs")

  return Array.isArray(tabs) ? [...tabs] : []
}

const resetTabPreferences = (groups) => {
  if (!window.acf?.setPreference) {
    return
  }

  const tabIndexes = getResettableTabIndexes(groups)

  if (tabIndexes.length === 0) {
    return
  }

  const tabs = getStoredTabs()

  tabIndexes.forEach((index) => {
    tabs[index] = 0
  })

  window.acf.setPreference("this.tabs", tabs)
}

export default function initAcfTabPreferenceReset() {
  const config = window[CONFIG_NAME]
  const groups = Array.isArray(config?.groups) ? config.groups : []

  if (!window.acf || groups.length === 0) {
    return
  }

  let shouldResetOnUnload = false

  window.acf.addAction("submit", () => {
    shouldResetOnUnload = true
  })

  window.acf.addAction(
    "unload",
    () => {
      if (!shouldResetOnUnload) {
        return
      }

      resetTabPreferences(groups)
    },
    99
  )
}
