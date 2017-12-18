<?php
/**
 * Alert class
 * Implements the Bootstrap "Alert" functionality. This can be a static block of text, or can alternately have a close
 * button that automatically hides the alert.
 *
 * Per Bootstraps documentation, you MUST specify an alert type class. Do this by using AddCssClass, or the CssClass
 * Attribute with a plus in front of the class. For example:
 * 	$objAlert->CssClass = '+' . Bootstrap::AlertSuccess;
 *
 * Use Display or Visible to show or hide the alert as needed. Or, set the
 * Dismissible attribute.
 *
 * Since its a QPanel, you can put text, template or child controls in it.
 *
 * By default, alerts will fade on close. Remove the fade class if you want to turn this off.
 *
 * Call Close() to close the dialog manually.
 *
 *
 * @property bool $Dismissible 
 * @property bool $HasCloseButton  
 */
namespace QCubed\Plugin\Bootstrap;

use \QType;

/**
 * Class Alert_CloseEvent
 * Event is fired just before an alert is closed, when the button is clicked.
 *
 * @package QCubed\Plugin\Bootstrap
 */
class Alert_ClosingEvent extends \QEvent {
	/** Event Name */
	const EventName = 'close.bs.alert';
}

/**
 * Class Alert_CloseEvent
 * Event is fired just after an alert is closed, after any animation is fired.
 *
 * @package QCubed\Plugin\Bootstrap
 */
class Alert_ClosedEvent extends \QEvent {
	/** Event Name */
	const EventName = 'closed.bs.alert';
}


class Alert extends \QPanel {
	protected $strCssClass = 'alert fade in';

	protected $blnDismissible = false;

	public function __construct ($objParent, $strControlId = null) {
		parent::__construct ($objParent, $strControlId);

		$this->SetHtmlAttribute("role", "alert");
		Bootstrap::LoadJS($this);
	}

	protected function GetInnerHtml() {
		$strText = parent::GetInnerHtml();

		if ($this->blnDismissible) {
			$strText = \Qhtml::RenderTag('button',
				['type'=>'button',
				'class'=>'close',
				'data-dismiss'=>'alert',
				'aria-label'=>"Close",
				],
				'<span aria-hidden="true">&times;</span>', false, true)
			. $strText;
		}
		return $strText;
	}

	public function GetEndScript() {
		if ($this->blnDismissible) {
			\QApplication::ExecuteControlCommand($this->ControlId, 'on', 'closed.bs.alert',
				new \QJsClosure("qcubed.recordControlModification ('{$this->ControlId}', '_Visible', false)"), \QJsPriority::High);
		}
		return parent::GetEndScript();
	}

	/**
	 * Closes the alert using the Bootstrap javascript mechanism to close it. Removes the alert from the DOM.
	 * Bootstrap has no mechanism for showing it again, so you will need
	 * to redraw the control to show it.
	 */
	public function Close() {
		$this->blnVisible = false;
		\QApplication::ExecuteControlCommand($this->ControlId, 'alert', 'close');
	}

	public function __get($strName) {
		switch ($strName) {
			case "Dismissible":
			case "HasCloseButton": // QCubed synonym
				return $this->blnDismissible;

			default:
				try {
					return parent::__get($strName);
				} catch (QCallerException $objExc) {
					$objExc->IncrementOffset();
					throw $objExc;
				}
		}
	}

	public function __set($strName, $mixValue) {
		switch ($strName) {
			case 'Dismissible':
			case "HasCloseButton": // QCubed synonym
				$blnDismissible = QType::Cast($mixValue, QType::Boolean);
				if ($blnDismissible != $this->blnDismissible) {
					$this->blnDismissible = $blnDismissible;
					$this->blnModified = true;
					if ($blnDismissible) {
						$this->AddCssClass(Bootstrap::AlertDismissible);
						Bootstrap::LoadJS($this);
					} else {
						$this->RemoveCssClass(Bootstrap::AlertDismissible);
					}
				}
				break;

			case '_Visible':	// Private attribute to record the visible state of the alert
				$this->blnVisible = $mixValue;
				break;


			default:
				try {
					parent::__set($strName, $mixValue);
				} catch (QCallerException $objExc) {
					$objExc->IncrementOffset();
					throw $objExc;
				}
				break;
		}
	}

}
