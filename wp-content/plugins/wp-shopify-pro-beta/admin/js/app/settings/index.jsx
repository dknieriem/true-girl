import React from 'react'
import { SettingsProvider } from './_state/provider'
import { SettingsGeneral } from './general'
import { SettingsSyncing } from './syncing'
import { SettingsPricing } from './pricing'
import { SettingsProducts } from './products'
import { SettingsSearch } from './search'
import { SettingsCart } from './cart'
import { SettingsLayout } from './layout'


function Settings() {
   return (
      <SettingsProvider>
         <SettingsGeneral />
         <SettingsSyncing />
         <SettingsPricing />
         <SettingsLayout />
         <SettingsProducts />
         <SettingsSearch />
         <SettingsCart />
      </SettingsProvider>
   )
}

export { Settings }
