function availableClientComponents() {
   return '[data-wps-is-client-component-wrapper]'
}

function availableProductBuyButtonSelector() {
   return '[data-wps-render-from-server="0"][data-wps-component-template*="components/products/buy-button"]'
}

function availableCartButtonComponentsSelector() {
   return '[data-wps-is-client-component-wrapper][data-wps-client-component-type="cart"]'
}

function availableSearchComponentsSelector() {
   return '[data-wps-is-client-component-wrapper][data-wps-client-component-type="search"]'
}

function availableStorefrontComponentsSelector() {
   return '[data-wps-is-client-component-wrapper][data-wps-client-component-type="storefront"]'
}

function availableProductsSelector() {
   return '[data-wps-is-client-component-wrapper][data-wps-client-component-type="products"]'
}

function availableCollectionsComponentsSelector() {
   return '[data-wps-is-client-component-wrapper][data-wps-client-component-type="collections"]'
}

function elementByGIDSelector(gid) {
   return '[data-wps-is-component-wrapper][data-wps-gid="' + gid + '"]'
}

function getAvailableProductBuyButtons() {
   return document.querySelectorAll(availableProductBuyButtonSelector())
}

function getClientComponents() {
   return document.querySelectorAll(availableClientComponents())
}

function getAvailableProducts() {
   return document.querySelectorAll(availableProductsSelector())
}

function getElementByGID(gid) {
   return document.querySelector(elementByGIDSelector(gid))
}

function getAvailableCartButtons() {
   return document.querySelectorAll(availableCartButtonComponentsSelector())
}

function getAvailableSearch() {
   return document.querySelectorAll(availableSearchComponentsSelector())
}

function getAvailableStorefront() {
   return document.querySelectorAll(availableStorefrontComponentsSelector())
}

function getAvailableCollections() {
   return document.querySelectorAll(availableCollectionsComponentsSelector())
}

export { getAvailableProducts, getAvailableCartButtons, getAvailableProductBuyButtons, getElementByGID, getAvailableSearch, getAvailableStorefront, getClientComponents, getAvailableCollections }
