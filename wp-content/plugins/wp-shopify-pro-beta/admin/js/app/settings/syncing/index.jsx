import React from 'react'
import ReactDOM from 'react-dom'
import { SyncingIsLiteSync } from './is-lite-sync'
import { SyncingIsSyncingPosts } from './is-syncing-posts'
import { SelectiveSyncAll } from './selective-sync-all'
import { SelectiveSyncProducts } from './selective-sync-products'
import { SelectiveSyncCollections } from './selective-sync-collections'
import { SelectiveSyncCustomers } from './selective-sync-customers'
import { SelectiveSyncOrders } from './selective-sync-orders'

function SettingsSyncing() {
   return (
      <>
         {ReactDOM.createPortal(<SyncingIsLiteSync />, document.getElementById('wps-settings-is-lite-sync'))}
         {ReactDOM.createPortal(<SyncingIsSyncingPosts />, document.getElementById('wps-settings-is-syncing-posts'))}
         {ReactDOM.createPortal(<SelectiveSyncAll />, document.getElementById('wps-settings-selective-sync-all'))}
         {ReactDOM.createPortal(<SelectiveSyncProducts />, document.getElementById('wps-settings-selective-sync-products'))}
         {ReactDOM.createPortal(<SelectiveSyncCollections />, document.getElementById('wps-settings-selective-sync-collections'))}
         {ReactDOM.createPortal(<SelectiveSyncCustomers />, document.getElementById('wps-settings-selective-sync-customers'))}
         {ReactDOM.createPortal(<SelectiveSyncOrders />, document.getElementById('wps-settings-selective-sync-orders'))}
      </>
   )
}

export { SettingsSyncing }
