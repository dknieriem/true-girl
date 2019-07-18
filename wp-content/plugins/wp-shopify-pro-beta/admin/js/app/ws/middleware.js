import concat from 'lodash/concat'

import { getSmartCollectionsCount, getSmartCollections, getCustomCollectionsCount, getCustomCollections } from './api/api-collections'

import { getShop, getShopCount } from './api/api-shop'

import { getProducts, getProductsCount } from './api/api-products'

import { getCollects, getCollectsCount } from './api/api-collects'

import { getOrders, getOrdersCount } from './api/api-orders'

import { getCustomers, getCustomersCount } from './api/api-customers'

import { registerWebhooks, getWebhooksCount } from './api/api-webhooks'

import { streamItems } from './streaming'

import { convertArrayWrapToObject } from '../utils/utils'

import { isSyncingProducts, isSyncingCollects, isSyncingOrders, isSyncingCustomers, isSyncingCollections, isSyncingShop } from '../globals/globals-syncing'

import { isLiteSync } from '../globals/globals-settings'

function liteSync(counts) {
   var promises = []

   if (isSyncingShop()) {
      promises = concat(promises, streamItems(counts.shop, getShop))
   }

   if (isSyncingProducts()) {
      promises = concat(promises, streamItems(counts.products, getProducts))
   }

   return promises
}

function fullSync(counts, inital) {
   var promises = []

   if (isSyncingCollections()) {
      promises = concat(promises, streamItems(counts.smart_collections, getSmartCollections))
      promises = concat(promises, streamItems(counts.custom_collections, getCustomCollections))
   }

   if (isSyncingShop()) {
      promises = concat(promises, streamItems(counts.shop, getShop))
   }

   if (isSyncingProducts()) {
      promises = concat(promises, streamItems(counts.products, getProducts))
   }

   if (isSyncingCollects()) {
      promises = concat(promises, streamItems(counts.collects, getCollects))
   }

   /* @if NODE_ENV='pro' */
   if (isSyncingOrders()) {
      promises = concat(promises, streamItems(counts.orders, getOrders))
   }

   if (isSyncingCustomers()) {
      promises = concat(promises, streamItems(counts.customers, getCustomers))
   }

   if (inital) {
      promises = concat(promises, registerWebhooks())
   }
   /* @endif */

   return Promise.all(promises)
}

/*

Syncing Shopify data with WordPress CPT

Each Promise here loops through the counted number of items and kicks off
the batch process

*/
function syncPluginData(counts, inital = false) {
   counts = convertArrayWrapToObject(counts)

   return fullSync(counts, inital)
}

function getLiteSyncCounts() {
   var promises = []

   promises = concat(promises, getShopCount())
   promises = concat(promises, getProductsCount())

   return Promise.all(promises)
}

function getFullSyncCounts() {
   var promises = []

   if (isSyncingCollections()) {
      promises = concat(promises, getSmartCollectionsCount())
      promises = concat(promises, getCustomCollectionsCount())
   }

   if (isSyncingShop()) {
      promises = concat(promises, getShopCount())
   }

   if (isSyncingProducts()) {
      promises = concat(promises, getProductsCount())
   }

   if (isSyncingCollects()) {
      promises = concat(promises, getCollectsCount())
   }

   /* @if NODE_ENV='pro' */
   if (isSyncingOrders()) {
      promises = concat(promises, getOrdersCount())
   }

   if (isSyncingCustomers()) {
      promises = concat(promises, getCustomersCount())
   }

   promises = concat(promises, getWebhooksCount())
   /* @endif */

   return Promise.all(promises)
}

/*

Syncing Shopify data with WordPress CPT

*/
function getItemCounts() {
   if (isLiteSync()) {
      return getLiteSyncCounts()
   }

   return getFullSyncCounts()
}

export { syncPluginData, getItemCounts }
