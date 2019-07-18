import { CheckboxControl } from '@wordpress/components'
import React from 'react'
import ReactDOM from 'react-dom'
import { toBoolean } from '../../utils/utils'

/*

<ProductsHeading />

*/
class ProductsImagesShowZoom extends React.Component {
   state = {
      checked: toBoolean(WP_Shopify.settings.productsImagesShowZoom)
   }

   onChangeHandle = checked => {
      this.setState({ checked: !this.state.checked })
   }

   render() {
      return <CheckboxControl checked={this.state.checked} onChange={this.onChangeHandle} />
   }
}

/*

Init <ProductsImagesShowZoom />

*/
function initProductsImagesShowZoom() {
   ReactDOM.render(<ProductsImagesShowZoom />, document.getElementById('wps-settings-products-images-show-zoom'))
}

export { initProductsImagesShowZoom }
