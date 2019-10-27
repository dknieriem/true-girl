import React, { useContext, useState, useEffect, useRef } from 'react'
import { SettingsContext } from '../_state/context'
import { CheckboxControl } from '@wordpress/components'
import { toBoolean } from '../../utils/utils'

function SelectiveSyncCollections() {
   const [settingsState, settingsDispatch] = useContext(SettingsContext)
   const [isChecked, setIsChecked] = useState(toBoolean(settingsState.selectiveSyncCollections))
   const [isDisabled, setIsDisabled] = useState(toBoolean(settingsState.isLiteSync))
   const isFirstRender = useRef(true)

   function onChange() {
      setIsChecked(!isChecked)
      settingsDispatch({ type: 'UPDATE_SETTING', payload: { key: 'selectiveSyncCollections', value: !isChecked } })
      settingsDispatch({ type: 'UPDATE_SETTING', payload: { key: 'selectiveSyncAll', value: false } })
   }

   useEffect(() => {
      if (settingsState.isLiteSync) {
         setIsDisabled(true)
      } else {
         if (settingsState.selectiveSyncAll) {
            setIsChecked(false)
            setIsDisabled(true)
         } else {
            setIsDisabled(false)
         }
      }
   }, [settingsState.isLiteSync, settingsState.selectiveSyncAll])

   return <CheckboxControl label='Collections' disabled={isDisabled} checked={isChecked} onChange={onChange} />
}

export { SelectiveSyncCollections }
