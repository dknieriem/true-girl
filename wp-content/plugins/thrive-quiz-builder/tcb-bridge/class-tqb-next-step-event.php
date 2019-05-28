<?php

class TQB_Thrive_Next_Step extends TCB_Event_Action_Abstract {

	/**
	 * Should return the user-friendly name for this Action
	 *
	 * @return string
	 */
	public function getName() {
		return 'Next Step in Quiz';
	}

	/**
	 * Should output the settings needed for this Action when a user selects it from the list
	 */
	public function renderSettings( $data ) {
		return 'When your visitors click on this element, they will be redirected to the next step set by you in the quiz structure.';
	}

	/**
	 * Should return an actual string containing the JS function that's handling this action.
	 * The function will be called with 3 parameters:
	 *      -> event_trigger (e.g. click, dblclick etc)
	 *      -> action_code (the action that's being executed)
	 *      -> config (specific configuration for each specific action - the same configuration that has been setup in the settings section)
	 *
	 * Example (php): return 'function (trigger, action, config) { console.log(trigger, action, config); }';
	 *
	 * The function will be called in the context of the element
	 *
	 * The output MUST be a valid JS function definition.
	 *
	 * @return string the JS function definition (declaration + body)
	 */
	public function getJsActionCallback() {

		return '';
	}

	public function getSummary() {

	}

	/**
	 * should check if the current action is available to be displayed in the lists inside the event manager
	 * @return bool
	 */
	public function isAvailable() {

		return true;
	}
}
