import React from 'react'
import ReactDOM from 'react-dom'
import { LayoutAlignHeight } from './align-height'

function SettingsLayout() {
   return (
      <>
         {ReactDOM.createPortal(<LayoutAlignHeight />, document.getElementById('wps-settings-align-height'))}
      </>
   )
}

export { SettingsLayout }
