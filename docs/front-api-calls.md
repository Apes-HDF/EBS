# API calls from the Front

To make an API call from the Front you've got to use native [fetch](https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API/Using_Fetch) 
function in a [Stimulus](https://stimulus.hotwired.dev/) controller.

Do not use hard-coded path in your Controller. Get the path from the back in your
twig template and forward it to your Stimulus Controller with a [Value attribute](https://stimulus.hotwired.dev/reference/values).

Example for the product component:

```html
<!-- templates/components/item/_product.html.twig -->
data-product-route-value="{{ path('_api_/product/{id}/switchStatus_post', {id: product.id}) }}"
```

```javascript
// assets/controllers/product_controller.js
const response = await fetch(this.routeValue, { method: 'POST' })
```
