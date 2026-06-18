const $ = window.jQuery ?? window.$

export default function initAcfDatePickerFields() {
  if (!window.acf) {
    return
  }

  acf.addAction("date_picker_init", ($input, args) => {
    const $field = $input.closest(".acf-field")
    const minDateAttr = $field.data("min-date")
    const maxDateAttr = $field.data("max-date")
    const linkedField = $field.data("linked-field")

    let baseArgs = $.extend({}, args)

    if (minDateAttr) {
      baseArgs.minDate = minDateAttr === "today" ? new Date() : minDateAttr
    }

    if (maxDateAttr) {
      baseArgs.maxDate = maxDateAttr === "today" ? new Date() : maxDateAttr
    }

    if (linkedField) {
      const $linkedField = $(`[data-name="${linkedField}"]`)
      const $linkedInput = $linkedField.find("input")

      const updateMinDate = function () {
        const linkedValue = $linkedInput.val()

        if (!linkedValue) {
          return
        }

        const year = linkedValue.substring(0, 4)
        const month = linkedValue.substring(4, 6) - 1
        const day = linkedValue.substring(6, 8)
        const linkedDate = new Date(year, month, day)

        $input.datepicker("option", "minDate", linkedDate)
      }

      updateMinDate()
      $linkedInput.on("change", updateMinDate)
      baseArgs.beforeShow = updateMinDate
    }

    $input.datepicker("destroy").datepicker(baseArgs)
  })
}
