import React from 'react'
import { AdminReducer } from './reducer'
import { AdminInitialState } from './initial-state'
import { AdminContext } from './context'

function AdminProvider(props) {
   const [state, dispatch] = React.useReducer(AdminReducer, AdminInitialState(props))

   const value = React.useMemo(() => [state, dispatch], [state])

   return <AdminContext.Provider value={value} {...props} />
}

export { AdminProvider }
