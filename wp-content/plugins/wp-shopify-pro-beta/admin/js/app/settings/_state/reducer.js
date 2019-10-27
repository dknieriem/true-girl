import update from 'immutability-helper'

function SettingsReducer(state, action) {
   switch (action.type) {
      case 'UPDATE_SETTING':
         const newState = {
            ...state
         }

         newState[action.payload.key] = update(state[action.payload.key], { $set: action.payload.value })

         return newState

      default:
         return state
   }
}

export { SettingsReducer }
