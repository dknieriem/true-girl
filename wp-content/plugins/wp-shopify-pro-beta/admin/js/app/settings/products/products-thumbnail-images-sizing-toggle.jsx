import React, { useContext, useState } from 'react'
import { SettingsContext } from '../_state/context'
import { FormToggle } from '@wordpress/components'
import { toBoolean } from '../../utils/utils'

function ProductsThumbnailToggle() {
   const [settingsState, settingsDispatch] = useContext(SettingsContext)
   const [isChecked, setIsChecked] = useState(toBoolean(settingsState.productsThumbnailImagesSizingToggle))

   function onChange() {
      setIsChecked(!isChecked)
      settingsDispatch({ type: 'UPDATE_SETTING', payload: { key: 'productsThumbnailImagesSizingToggle', value: !isChecked } })
   }

   return <FormToggle checked={isChecked} onChange={onChange} />
}

export { ProductsThumbnailToggle }
