import React from 'react'
import ReactDOM from 'react-dom'
import to from 'await-to-js'
import { Notice } from '/Users/andrew/www/devil/devilbox-new/data/www/wpshopify-components'
import { deleteCacheContains, turnOffCacheCleared } from '/Users/andrew/www/devil/devilbox-new/data/www/wpshopify-api'
import { getComponentUserOptions } from '../common/api'
import { App } from './app'

async function bootstrap() {
   var noticeElement = document.querySelector(WP_Shopify.settings.layout.globalNoticesDropzone)
   var shopElement = document.querySelector('#wps-shop')

   if (WP_Shopify.misc.cache_cleared) {
      deleteCacheContains('wps-component-options')

      try {
         turnOffCacheCleared()
      } catch (err) {
         console.error('wpshopify error 💩 ', err)
      }
   }

   const [error, componentOptions] = await to(getComponentUserOptions())

   if (error) {
      if (noticeElement) {
         ReactDOM.render(<Notice message={error} type='warning' />, noticeElement)
      } else {
         ReactDOM.render(<Notice message={error} type='warning' />, shopElement)
      }

      document.body.classList.add('wps-has-bootstrap-error')

      return
   }

   if (!componentOptions) {
      if (noticeElement) {
         ReactDOM.render(<Notice message={'No component options found'} type='warning' />, noticeElement)
      } else {
         ReactDOM.render(<Notice message={'No component options found'} type='warning' />, shopElement)
      }

      document.body.classList.add('wps-has-bootstrap-error')

      return
   }

   ReactDOM.render(
      <App
         productOptions={componentOptions.products}
         cartOptions={componentOptions.cart}
         searchOptions={componentOptions.search}
         storefrontOptions={componentOptions.storefront}
         collectionsOptions={componentOptions.collections}
      />,
      document.querySelector('#wps-shop')
   )

   document.body.classList.add('wps-is-bootstrapped')
}

export { bootstrap }
