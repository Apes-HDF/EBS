import { startStimulusApp } from '@symfony/stimulus-bridge'

import PasswordVisibility from 'stimulus-password-visibility'
import Carousel from 'stimulus-carousel'
import 'swiper/swiper-bundle.css'

// Registers Stimulus controllers from controllers.json and in the controllers/ directory
export const app = startStimulusApp(require.context(
  '@symfony/stimulus-bridge/lazy-controller-loader!./controllers',
  true,
  /\.[jt]sx?$/
))

// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);

app.register('carousel', Carousel)
app.register('password-visibility', PasswordVisibility)
