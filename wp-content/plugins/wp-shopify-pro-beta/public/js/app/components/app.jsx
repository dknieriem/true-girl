import React, { Suspense } from 'react'
import { Shop, Cart, Products, Storefront, Search, Collections, Items } from '/Users/andrew/www/devil/devilbox-new/data/www/wpshopify-components'

function App({ cartOptions, productOptions, searchOptions, storefrontOptions, collectionsOptions }) {
   return (
      <Suspense fallback=''>
         <Shop>
            <Cart options={cartOptions} />

            <Items options={productOptions}>
               <Products />
            </Items>

            <Items options={collectionsOptions}>
               <Collections />
            </Items>

            <Items options={storefrontOptions}>
               <Storefront />
            </Items>

            <Items options={searchOptions}>
               <Search />
            </Items>
         </Shop>
      </Suspense>
   )
}

export { App }
