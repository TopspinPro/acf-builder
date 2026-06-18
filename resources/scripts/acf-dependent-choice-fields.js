const $ = window.jQuery ?? window.$

export default function initAcfDependentChoiceFields() {
  const configs = window.tsp_acf_dependent_choice_fields || {}

  if (!window.acf || Object.keys(configs).length === 0) {
    return
  }

  const getFieldWrapper = (fieldName) =>
    $(`.acf-field[data-name="${fieldName}"]`).first()

  const getControllerValue = ($field) => {
    const $select = $field.find("select").first()

    if ($select.length) {
      const value = $select.val()

      return typeof value === "string" ? value : ""
    }

    const $checkedInput = $field.find(
      "input[type='radio']:checked, input[type='checkbox']:checked"
    ).first()

    if ($checkedInput.length) {
      const value = $checkedInput.val()

      return typeof value === "string" ? value : ""
    }

    const $textInput = $field.find("input[type='text']").first()

    if ($textInput.length) {
      const value = $textInput.val()

      return typeof value === "string" ? value : ""
    }

    return ""
  }

  const getSelectedCheckboxValues = ($field) =>
    $field
      .find("input[type='checkbox']:checked")
      .map((index, input) => $(input).val())
      .get()
      .filter((value) => typeof value === "string" && value !== "")

  const ensureHiddenCheckboxInput = ($field, fieldKey) => {
    const hiddenName = `acf[${fieldKey}]`
    let $hidden = $field.find(`input[type="hidden"][name="${hiddenName}"]`).first()

    if ($hidden.length) {
      return
    }

    $hidden = $('<input type="hidden" value="">').attr("name", hiddenName)
    $field.find(".acf-input").first().prepend($hidden)
  }

  const renderCheckboxChoices = ($field, choices) => {
    const fieldKey = $field.data("key")
    const $list = $field.find(".acf-checkbox-list").first()

    if (!$list.length || !fieldKey) {
      return
    }

    ensureHiddenCheckboxInput($field, fieldKey)

    const inputName = `acf[${fieldKey}][]`
    const selectedValues = new Set(getSelectedCheckboxValues($field))
    const listTagName = ($list.prop("tagName") || "div").toLowerCase()

    $list.empty()

    Object.entries(choices).forEach(([value, label]) => {
      const isChecked = selectedValues.has(value)
      const $label = $("<label>")
      const $checkbox = $('<input type="checkbox">')
        .attr("name", inputName)
        .attr("value", value)
        .prop("checked", isChecked)

      $label.append($checkbox).append(document.createTextNode(` ${label}`))

      if (listTagName === "ul" || listTagName === "ol") {
        $list.append($("<li>").append($label))
        return
      }

      $list.append($label)
    })
  }

  const renderSelectChoices = ($field, choices) => {
    const $select = $field.find("select").first()

    if (!$select.length) {
      return
    }

    const currentValue = typeof $select.val() === "string" ? $select.val() : ""

    $select.find("option").remove()
    $select.append($('<option value="">'))

    Object.entries(choices).forEach(([value, label]) => {
      const $option = $("<option>")
        .attr("value", value)
        .text(label)

      if (value === currentValue) {
        $option.prop("selected", true)
      }

      $select.append($option)
    })

    if ($select.data("select2")) {
      $select.trigger("change.select2")
    }
  }

  const syncField = (config) => {
    const $targetField = getFieldWrapper(config.targetFieldName)
    const $controllerField = getFieldWrapper(config.controllerFieldName)

    if (!$targetField.length || !$controllerField.length) {
      return
    }

    const controllerValue = getControllerValue($controllerField)
    const choices = config.choicesByControllerValue?.[controllerValue] || {}

    if (config.fieldType === "checkbox") {
      renderCheckboxChoices($targetField, choices)
      return
    }

    if (config.fieldType === "select") {
      renderSelectChoices($targetField, choices)
    }
  }

  const syncAllFields = () => {
    Object.values(configs).forEach(syncField)
  }

  const scheduleSync = () => {
    syncAllFields()
    setTimeout(syncAllFields, 0)
    setTimeout(syncAllFields, 150)
  }

  acf.addAction("ready", scheduleSync)
  acf.addAction("append", scheduleSync)
  acf.addAction("remove", scheduleSync)

  Object.values(configs).forEach((config) => {
    const selector = `.acf-field[data-name="${config.controllerFieldName}"] select, .acf-field[data-name="${config.controllerFieldName}"] input`

    $(document).on("change", selector, () => {
      syncField(config)
    })
  })

  $(scheduleSync)
}
