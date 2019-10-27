import React, { useContext, useState, useEffect, useRef } from 'react'
import { CheckboxControl } from '@wordpress/components'
import { toBoolean } from '../../utils/utils'
import { SettingsContext } from '../_state/context'

function SyncingIsSyncingPosts() {
   const [settingsState, settingsDispatch] = useContext(SettingsContext)
   const [isChecked, setIsChecked] = useState(toBoolean(checkedOrNot()))
   const [isDisabled, setIsDisabled] = useState(toBoolean(settingsState.isLiteSync))
   const isFirstRender = useRef(true)

   function checkedOrNot() {
      if (settingsState.isLiteSync) {
         return false
      }

      return settingsState.isSyncingPosts
   }

   function onChange() {
      setIsChecked(!isChecked)
      settingsDispatch({ type: 'UPDATE_SETTING', payload: { key: 'isSyncingPosts', value: !isChecked } })
   }

   useEffect(() => {
      if (isFirstRender.current) {
         isFirstRender.current = false
         return
      }

      if (settingsState.isLiteSync) {
         setIsDisabled(true)
      } else {
         setIsDisabled(false)
      }
   }, [settingsState.isLiteSync])

   return <CheckboxControl disabled={isDisabled} checked={isChecked} onChange={onChange} />
}

export { SyncingIsSyncingPosts }
