import React from 'react'
import { AdminProvider } from './_state/provider'
import { Settings } from '../settings'

function Admin(props) {
   
   return (
      <>
         <AdminProvider options={props}>
            <Settings />
         </AdminProvider>
      </>
   )
}

export { Admin }
