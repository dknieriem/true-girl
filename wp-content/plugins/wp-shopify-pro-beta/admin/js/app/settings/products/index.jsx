import React from 'react'
import ReactDOM from 'react-dom'

import { ProductsThumbnailToggle } from './products-thumbnail-images-sizing-toggle'
import { ProductsThumbnailWidth } from './products-thumbnail-images-sizing-width'
import { ProductsThumbnailHeight } from './products-thumbnail-images-sizing-height'
import { ProductsThumbnailCrop } from './products-thumbnail-images-sizing-crop'
import { ProductsThumbnailScale } from './products-thumbnail-images-sizing-scale'

function SettingsProducts() {
   return (
      <>
         {ReactDOM.createPortal(<ProductsThumbnailToggle />, document.getElementById('wps-settings-products-thumbnail-images-sizing-toggle'))}
         {ReactDOM.createPortal(<ProductsThumbnailWidth />, document.getElementById('wps-settings-products-thumbnail-images-sizing-width'))}
         {ReactDOM.createPortal(<ProductsThumbnailHeight />, document.getElementById('wps-settings-products-thumbnail-images-sizing-height'))}
         {ReactDOM.createPortal(<ProductsThumbnailCrop />, document.getElementById('wps-settings-products-thumbnail-images-sizing-crop'))}
         {ReactDOM.createPortal(<ProductsThumbnailScale />, document.getElementById('wps-settings-products-thumbnail-images-sizing-scale'))}
      </>
   )
}

export { SettingsProducts }
