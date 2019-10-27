import React, { useContext, useState } from 'react'
import { SettingsContext } from '../_state/context'
import { TextControl } from '@wordpress/components'

function ProductsThumbnailWidth() {
   const [settingsState, settingsDispatch] = useContext(SettingsContext)
   const [fieldValue, setFieldValue] = useState(settingsState.productsThumbnailImagesSizingWidth)

   function onChange(fieldValue) {
      setFieldValue(fieldValue)
      settingsDispatch({ type: 'UPDATE_SETTING', payload: { key: 'productsThumbnailImagesSizingWidth', value: fieldValue } })
   }

   return <TextControl type='number' disabled={!settingsState.productsThumbnailImagesSizingToggle} value={fieldValue} onChange={onChange} />
}

export { ProductsThumbnailWidth }
