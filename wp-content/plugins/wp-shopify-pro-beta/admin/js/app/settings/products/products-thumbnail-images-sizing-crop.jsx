import React, { useContext, useState } from 'react'
import { SettingsContext } from '../_state/context'
import { SelectControl } from '@wordpress/components'

function ProductsThumbnailCrop() {
   const [settingsState, settingsDispatch] = useContext(SettingsContext)
   const [fieldValue, setFieldValue] = useState(settingsState.productsThumbnailImagesSizingCrop)

   function onChange(fieldValue) {
      setFieldValue(fieldValue)
      settingsDispatch({ type: 'UPDATE_SETTING', payload: { key: 'productsThumbnailImagesSizingCrop', value: fieldValue } })
   }

   return (
      <SelectControl
         value={fieldValue}
         disabled={!settingsState.productsThumbnailImagesSizingToggle}
         options={[
            {
               label: 'None',
               value: 'none'
            },
            {
               label: 'Top',
               value: 'top'
            },
            {
               label: 'Center',
               value: 'center'
            },
            {
               label: 'Bottom',
               value: 'bottom'
            },
            {
               label: 'Left',
               value: 'left'
            },
            {
               label: 'Right',
               value: 'right'
            }
         ]}
         onChange={onChange}
      />
   )
}

export { ProductsThumbnailCrop }
