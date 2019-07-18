import React, { useContext, useState } from 'react'
import { SettingsContext } from '../_state/context'
import { CheckboxControl } from '@wordpress/components'
import { toBoolean } from '../../utils/utils'

function GeneralDisableDefaultPages() {
   const [settingsState, settingsDispatch] = useContext(SettingsContext)
   const [isChecked, setIsChecked] = useState(toBoolean(settingsState.disableDefaultPages))

   function onChange() {
      setIsChecked(!isChecked)
      settingsDispatch({ type: 'UPDATE_SETTING', payload: { key: 'disableDefaultPages', value: !isChecked } })
   }

   return <CheckboxControl checked={isChecked} onChange={onChange} />
}

export { GeneralDisableDefaultPages }
