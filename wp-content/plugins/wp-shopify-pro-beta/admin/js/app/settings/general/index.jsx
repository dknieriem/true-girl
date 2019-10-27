import React from 'react'
import ReactDOM from 'react-dom'
import { GeneralDisableDefaultPages } from './disable-default-pages'

function SettingsGeneral() {
   return <>{ReactDOM.createPortal(<GeneralDisableDefaultPages />, document.getElementById('wps-settings-disable-default-pages'))}</>
}

export { SettingsGeneral }
