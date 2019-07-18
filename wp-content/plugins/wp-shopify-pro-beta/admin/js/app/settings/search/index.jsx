import React from 'react'
import ReactDOM from 'react-dom'
import { SearchBy } from './search-by'
import { SearchExactMatch } from './exact-match'

function SettingsSearch() {
   return (
      <>
         {ReactDOM.createPortal(<SearchBy />, document.getElementById('wps-settings-search-by'))}
         {ReactDOM.createPortal(<SearchExactMatch />, document.getElementById('wps-settings-search-exact-match'))}
      </>
   )
}

export { SettingsSearch }
