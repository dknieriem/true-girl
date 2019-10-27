import React, { useContext, useState } from 'react'
import { SettingsContext } from '../_state/context'
import { RadioControl } from '@wordpress/components'

function SearchBy() {
   const [settingsState, settingsDispatch] = useContext(SettingsContext)
   const [selectedOption, setSelectedOption] = useState(settingsState.searchBy)

   function onChange(value) {
      setSelectedOption(value)
      settingsDispatch({ type: 'UPDATE_SETTING', payload: { key: 'searchBy', value: value } })
   }

   return (
      <div>
         <RadioControl
            selected={selectedOption}
            options={[
               {
                  label: 'Title',
                  value: 'title'
               },
               {
                  label: 'Tag',
                  value: 'tag'
               },
               {
                  label: 'Vendor',
                  value: 'vendor'
               },
               {
                  label: 'Product Type',
                  value: 'product_type'
               },
               {
                  label: 'Variants Title',
                  value: 'variants.title'
               }
            ]}
            onChange={onChange}
         />
      </div>
   )
}

export { SearchBy }
