import { Controller} from '@hotwired/stimulus'
import { Toast } from 'bootstrap'

export default class extends Controller {
  static targets = [ 'activeButton', 'pausedButton', 'activeTag', 'pausedTag' ]

  static values = {
    route: String,
  }

  async switchStatus() {
    const response = await fetch(this.routeValue, { method: 'PATCH' })

    if (!response.ok) {
      const toastElement = document.querySelector('[data-notification=error]')
      const toast = new Toast(toastElement)
      toast.show()
      return
    }

    const data = await response.json()
    const { status } = data

    const toastElement = document.querySelector(`[data-notification=${status}StatusSuccess]`)
    const toast = new Toast(toastElement)
    toast.show()

    this.activeButtonTarget.classList.toggle('d-none')
    this.pausedButtonTarget.classList.toggle('d-none')

    this.activeTagTarget.classList.toggle('d-none')
    this.pausedTagTarget.classList.toggle('d-none')
  }
}
