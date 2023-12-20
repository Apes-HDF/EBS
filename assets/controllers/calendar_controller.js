import { Controller} from '@hotwired/stimulus'
import flatpickr from 'flatpickr'
import rangePlugin from 'flatpickr/dist/plugins/rangePlugin'
import { French } from 'flatpickr/dist/l10n/fr'
export default class extends Controller {
  static values = {
    unavailabilities: String
  }

  initialize() {
    this.fp
    this.fpProductOwner
  }

  connect() {
    const unavailabilities = this.unavailabilitiesValue.split(',')

    const selector = this.element.querySelector('#calendar-start-day')

    const commonOptions = {
      locale: {
        ...French,
        weekdays: {
          shorthand: ['D', 'L', 'M', 'M', 'J', 'V', 'S'], // Override shorthand because it is initially in the "lun", "mar", "mer" (etc) format
          longhand: French.weekdays.longhand,
        }
      },
      inline: true,
      disable: unavailabilities,
      monthSelectorType: 'static',
      minDate: 'today',
    }

    if (!selector) {
      // This flatpickr instance is meant for when a user visits its own product page
      this.fpProductOwner = flatpickr('#product-owner-calendar', commonOptions)

      // This prevents owner from selecting days
      this.fpProductOwner.daysContainer.addEventListener('click', (event) => {
        event.stopPropagation()
      }, true)

      return
    }

    this.fp = flatpickr(selector, {
      ...commonOptions,
      allowInput: true,
      mode: 'range',
      plugins: [new rangePlugin({ input: '#calendar-end-day'})],
      onReady(_, __, instance) {
        let params = (new URL(location)).searchParams
        const startAt = params.get('startAt')
        const endAt = params.get('endAt')

        const dates = []

        if (startAt) dates.push(startAt)
        if (endAt) dates.push(endAt)

        if (dates.length) {
          instance.setDate(dates, true)
          return
        }

        const buttonServiceRequestPage = document.querySelector('#create_service_request_submit')

        if (!buttonServiceRequestPage) return

        buttonServiceRequestPage.setAttribute('disabled', '')
      },
      onChange() {
        const startAtInput = document.querySelector('#calendar-start-day')
        const endAtInput = document.querySelector('#calendar-end-day')
        const startAt = startAtInput.value
        const endAt = endAtInput.value

        const buttonProductPage = document.querySelector('#service-request')
        const buttonServiceRequestPage = document.querySelector('#create_service_request_submit')

        if (buttonProductPage) {
          startAt && endAt
            ? buttonProductPage.removeAttribute('disabled')
            : buttonProductPage.setAttribute('disabled', '')
        }

        if (buttonServiceRequestPage) {
          startAt && endAt
            ? buttonServiceRequestPage.removeAttribute('disabled')
            : buttonServiceRequestPage.setAttribute('disabled', '')
        }
      }
    })
  }

  resetDates() {
    this.fp.clear()
  }

  serviceRequest() {
    const button = this.element.querySelector('#service-request')
    const path = button.dataset.path
    const startAtInput = this.element.querySelector('#calendar-start-day')
    const endAtInput = this.element.querySelector('#calendar-end-day')
    const startAt = startAtInput.value
    const endAt = endAtInput.value

    const url = new URL(path, window.location.origin)
    url.searchParams.set('startAt', startAt)
    url.searchParams.set('endAt', endAt)

    location.href = url
  }
}
