import { Controller} from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['input', 'feedback']

  static values = {
    uploadMaxsizeByFile: Number, // max size by image allowed
    uploadMaxItems: Number, // max number of images allowed
    currentImagesCount: Number, // number of images already uploaded
    feedbackMessage: String, // image too too big feedback
    maxImagesError: String, // count threshold reached error
  }

  /**
   * 1. Check the size of each file. Remove the selected files if one is incorrect.
   * 2. Check the number of images uploaded.
   */
  checkUpload() {
    const maxAllowedImages = this.uploadMaxItemsValue - this.currentImagesCountValue
    if (maxAllowedImages < this.inputTarget.files.length) {
      this.feedbackTarget.innerHTML = this.maxImagesErrorValue
      this.inputTarget.value = ''

      return
    }

    const uploadMaxsizeByFile = 1048576 * this.uploadMaxsizeByFileValue
    let feedback = []
    for (let file of this.inputTarget.files) {
      if (file.size > uploadMaxsizeByFile) {
        feedback.push(this.feedbackMessageValue.replace('%file%', file.name))
      }
    }

    if (feedback.length > 0) {
      this.feedbackTarget.innerHTML = feedback.join('<br/>')
      this.inputTarget.value = ''
    }
  }
}
