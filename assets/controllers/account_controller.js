import { Controller } from '@hotwired/stimulus'

export default class extends Controller {

  hiddenUserInput () {
    const divUserInput = document.querySelector('.user-input')
    divUserInput.classList.add('hidden')
  }

  hiddenPlaceInput() {
    const divUserInput = document.querySelector('.place-input')
    divUserInput.classList.add('hidden')
  }
  connect () {
    const userInput = this.element.value === 'user'
    const placeInput = this.element.value === 'place'
    const inputUserLastname = document.querySelector('.input-lastname')
    const inputUserFirstname = document.querySelector('.input-firstname')
    const inputPlaceName = document.querySelector('.input-name')

    const userInputChecked = userInput && this.element.checked
    const placeInputChecked = placeInput && this.element.checked

    if (userInputChecked) {
      inputUserFirstname.required = true
      inputUserLastname.required = true
      this.hiddenPlaceInput()
    }

    if (placeInputChecked) {
      inputPlaceName.required = true
      this.hiddenUserInput()
    }
  }

  choosenType() {
    const placeInput = this.element.value === 'place'
    const inputUserLastname = document.querySelector('.input-lastname')
    const inputUserFirstname = document.querySelector('.input-firstname')
    const inputPlaceName = document.querySelector('.input-name')

    const divUserInput = document.querySelector('.user-input')
    const divPlaceInput = document.querySelector('.place-input')

    const placeInputChecked = placeInput && this.element.checked

    if (placeInputChecked) {
      inputUserFirstname.removeAttribute('required')
      inputUserLastname.removeAttribute('required')

      inputPlaceName.setAttribute('required', '')

      divPlaceInput.classList.remove('hidden')
      divUserInput.classList.add('hidden')
    } else {
      inputPlaceName.removeAttribute('required')

      inputUserFirstname.setAttribute('required', '')
      inputUserLastname.setAttribute('required', '')

      divUserInput.classList.remove('hidden')
      divPlaceInput.classList.add('hidden')
    }
  }
}
