import { toBoolean } from '../utils/utils'

const isSyncingAll = () => toBoolean(WP_Shopify.settings.selectiveSyncAll)

const isSyncingProducts = () => toBoolean(isSyncingAll() || WP_Shopify.settings.selectiveSyncProducts)

const isSyncingCollects = () => toBoolean(isSyncingAll() || WP_Shopify.settings.selectiveSyncProducts)

const isSyncingOrders = () => toBoolean(isSyncingAll() || WP_Shopify.settings.selectiveSyncOrders)

const isSyncingCustomers = () => toBoolean(isSyncingAll() || WP_Shopify.settings.selectiveSyncCustomers)

const isSyncingCollections = () => toBoolean(isSyncingAll() || WP_Shopify.settings.selectiveSyncCollections)

const isSyncingSmartCollections = () => toBoolean(isSyncingCollections())

const isSyncingCustomCollections = () => toBoolean(isSyncingCollections())

const isSyncingShop = () => toBoolean(isSyncingAll() || WP_Shopify.selective_sync.shop)

const isConnecting = () => toBoolean(WP_Shopify.isConnecting)

const isReconnectingWebhooks = () => toBoolean(WP_Shopify.reconnectingWebhooks)

const getSelectiveSync = () => WP_Shopify.selective_sync

export {
   isSyncingAll,
   isSyncingProducts,
   isSyncingCollects,
   isSyncingOrders,
   isSyncingCustomers,
   isSyncingCollections,
   isSyncingSmartCollections,
   isSyncingCustomCollections,
   isSyncingShop,
   isConnecting,
   isReconnectingWebhooks,
   getSelectiveSync
}
