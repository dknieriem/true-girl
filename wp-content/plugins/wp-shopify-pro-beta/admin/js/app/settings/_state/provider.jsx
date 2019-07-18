import React from 'react'
import { SettingsReducer } from './reducer'
import { SettingsInitialState } from './initial-state'
import { SettingsContext } from './context'

function SettingsProvider(props) {
   const [state, dispatch] = React.useReducer(SettingsReducer, SettingsInitialState(props))

   const value = React.useMemo(() => [state, dispatch], [state])

   return <SettingsContext.Provider value={value} {...props} />
}

export { SettingsProvider }
