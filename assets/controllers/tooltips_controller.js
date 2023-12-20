import { Controller } from '@hotwired/stimulus'
import { Tooltip } from 'bootstrap'

export default class extends Controller {
  connect() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new Tooltip(tooltipTriggerEl)
    })
  }
}
