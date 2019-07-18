import React, { useContext, useState } from 'react'
import { SettingsContext } from '../_state/context'
import { TextControl } from '@wordpress/components'

function ProductsThumbnailHeight() {
   const [settingsState, settingsDispatch] = useContext(SettingsContext)
   const [fieldValue, setFieldValue] = useState(settingsState.productsThumbnailImagesSizingHeight)

   function onChange(fieldValue) {
      setFieldValue(fieldValue)
      settingsDispatch({ type: 'UPDATE_SETTING', payload: { key: 'productsThumbnailImagesSizingHeight', value: fieldValue } })
   }

   return <TextControl type='number' disabled={!settingsState.productsThumbnailImagesSizingToggle} value={fieldValue} onChange={onChange} />
}

export { ProductsThumbnailHeight }
