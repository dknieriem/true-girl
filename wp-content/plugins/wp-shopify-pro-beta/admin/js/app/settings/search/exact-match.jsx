import React, { useContext, useState } from 'react'
import { SettingsContext } from '../_state/context'
import { CheckboxControl } from '@wordpress/components'
import { toBoolean } from '../../utils/utils'

function SearchExactMatch() {
   const [settingsState, settingsDispatch] = useContext(SettingsContext)
   const [isChecked, setIsChecked] = useState(toBoolean(settingsState.searchExactMatch))

   function onChange() {
      setIsChecked(!isChecked)
      settingsDispatch({ type: 'UPDATE_SETTING', payload: { key: 'searchExactMatch', value: !isChecked } })
   }

   return <CheckboxControl checked={isChecked} onChange={onChange} />
}

export { SearchExactMatch }
