import React, { useContext, useState, useEffect, useRef } from 'react'
import { SettingsContext } from '../_state/context'
import { CheckboxControl } from '@wordpress/components'
import { toBoolean } from '../../utils/utils'

function SelectiveSyncAll() {
   const [settingsState, settingsDispatch] = useContext(SettingsContext)
   const [isChecked, setIsChecked] = useState(toBoolean(settingsState.selectiveSyncAll))
   const [isDisabled, setIsDisabled] = useState(toBoolean(settingsState.isLiteSync))
   const isFirstRender = useRef(true)

   function onChange() {
      setIsChecked(true)
      settingsDispatch({ type: 'UPDATE_SETTING', payload: { key: 'selectiveSyncAll', value: !isChecked } })
   }

   useEffect(() => {
      if (settingsState.isLiteSync) {
         setIsDisabled(true)
      } else {
         if (!settingsState.selectiveSyncAll) {
            setIsChecked(false)
         } else {
            setIsChecked(true)
         }

         setIsDisabled(false)
      }
   }, [settingsState.isLiteSync, settingsState.selectiveSyncAll])

   return <CheckboxControl label='All' disabled={isDisabled} checked={isChecked} onChange={onChange} />
}

export { SelectiveSyncAll }
