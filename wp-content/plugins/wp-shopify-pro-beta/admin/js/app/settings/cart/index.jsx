import React from 'react'
import ReactDOM from 'react-dom'
import { CartEnableTerms } from './enable-terms'
import { CartTerms } from './terms'
import { CartEnableNotes } from './enable-notes'
import { CartNotesPlaceholder } from './notes-placeholder'

function SettingsCart() {
   return (
      <>
         {ReactDOM.createPortal(<CartEnableTerms />, document.getElementById('wps-settings-cart-enable-terms'))}
         {ReactDOM.createPortal(<CartTerms />, document.getElementById('wps-settings-cart-terms'))}
         {ReactDOM.createPortal(<CartEnableNotes />, document.getElementById('wps-settings-cart-enable-notes'))}
         {ReactDOM.createPortal(<CartNotesPlaceholder />, document.getElementById('wps-settings-cart-notes-placeholder'))}
      </>
   )
}

export { SettingsCart }
