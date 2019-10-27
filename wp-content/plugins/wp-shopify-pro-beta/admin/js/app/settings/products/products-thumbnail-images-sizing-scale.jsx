import React, { useContext, useState } from 'react'
import { SettingsContext } from '../_state/context'
import { SelectControl } from '@wordpress/components'

function ProductsThumbnailScale() {
   const [settingsState, settingsDispatch] = useContext(SettingsContext)
   const [fieldValue, setFieldValue] = useState(settingsState.productsThumbnailImagesSizingScale)

   function onChange(fieldValue) {
      setFieldValue(fieldValue)
      settingsDispatch({ type: 'UPDATE_SETTING', payload: { key: 'productsThumbnailImagesSizingScale', value: fieldValue } })
   }

   return <SelectControl value={fieldValue} disabled={!settingsState.productsThumbnailImagesSizingToggle} options={[{ value: 1, label: '1' }, { value: 2, label: '2' }, { value: 3, label: '3' }]} onChange={onChange} />
}

export { ProductsThumbnailScale }