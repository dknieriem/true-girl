import React, { useContext, useState } from 'react'
import { SettingsContext } from '../_state/context'
import { CheckboxControl } from '@wordpress/components'
import { toBoolean } from '../../utils/utils'

function SyncingIsLiteSync() {
   const [settingsState, settingsDispatch] = useContext(SettingsContext)
   const [isChecked, setIsChecked] = useState(toBoolean(settingsState.isLiteSync))

   function onChange() {
      setIsChecked(!isChecked)
      settingsDispatch({ type: 'UPDATE_SETTING', payload: { key: 'isLiteSync', value: !isChecked } })
   }

   return <CheckboxControl checked={isChecked} onChange={onChange} />
}

export { SyncingIsLiteSync }
