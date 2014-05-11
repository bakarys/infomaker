<?php

namespace Infomaker\Scripts
{

	/**
	 * This class generates entities;
	 * It should be called with the file containing the array
	 * and the class name as options
	 */
	class Scaffold
	{
		/**
		 * @var string
		 */
		private $modelAlias;

		public function __construct()
		{

		}

		/**
		 *
		 * @return Object $instance
		 */
		static public function getInstance()
		{
			static $instance = null;
			if ($instance === null) {
				$instance = new self;
			}

			return $instance;
		}

		/**
		 * This method is recursive and it generates sap entity classes
		 *
		 * @param array $values
		 * @param string $className
		 * @return void
		 */
		public function generateClass($values, $className)
		{
			$code = array(
				'<?php',
				'namespace SSENSE\SapBridge\Models\Entities',
				'{',
				'',
				' /**',
				' * Sap entity ' . $className,
				' */',
				' class ' . $className . ' ' . 'extends Base',
				' {'
			);

			$code[] = ''; // key 9
			$code[] = ''; // key 10

			$this->generateProperties($code, $values);
			$this->generatePropertyMethods($code, $values);

			if (!empty($this->modelAlias)) {
				$code[10] = ' protected $modelAlias = ' . $this->modelAlias . ';';
				$this->modelAlias = null;
			} else {
				unset($code[9], $code[10]);
			}

			$code[] = ' }';
			$code[] = '}';

			file_put_contents("app/models/entities/{$className}.php", implode(PHP_EOL, $code));

			echo "Generate file {$className}.php in models/entities/" . PHP_EOL;
		}

		/**
		 *
		 * @param array $code
		 * @param array $values
		 * @return void
		 */
		private function generateProperties(&$code, $values)
		{
			foreach ($values as $field => $val) {
				$code[] = '';
				$code[] = ' /**';
				$code[] = ' * ' . $field;
				$code[] = ' * @var ' . $this->getFieldType($val);
				$code[] = ' */';
				$code[] = ' public $' . $field . ';';
			}
		}

		/**
		 *
		 * @param array $code
		 * @param array $values
		 * @return void
		 */
		private function generatePropertyMethods(&$code, $values)
		{
			foreach ($values as $field => $val) {
				if (is_array($val)) {
					$chieldField = key($val);
					if (count($val[$chieldField]) > 1 && is_numeric(key($val[$chieldField]))) {
						$val[$chieldField] = $val[$chieldField][key($val[$chieldField])];
					}

					$this->modelAlias = "'{$chieldField}'";

					$this->generateClass($val[$chieldField], $chieldField); //recursive
				}

				$this->generateSetter($code, $field, $val);
				$this->generateGetter($code, $field, $val);
			}
		}

		/**
		 *
		 * @param array $code
		 * @param boolean|integer|float|string|array $field
		 * @param string|array $val
		 * @return void
		 */
		private function generateSetter(&$code, $field, $val)
		{
			$code[] = '';
			$code[] = ' /**';
			$code[] = ' * Setter for ' . $field;
			$code[] = ' *';
			$code[] = ' * @param ' . $this->getFieldType($val) . ' $' . $field;
			$code[] = ' * @return Void';
			$code[] = ' */';

			if (is_array($val)) {
				$chieldField = key($val);
				$code[] = ' public function set' . ucfirst($field) . '(array $' . $chieldField . ')';
				$code[] = ' {';
				$code[] = ' $this->typeErrorHandler(\'' . $this->getFieldType($val) . '\', $' . $chieldField . ');';
				$code[] = ' $this->' . $field . ' = new ' . $chieldField . '($' . $chieldField . ');';
				$code[] = ' }';
			} else {
				$code[] = ' public function set' . ucfirst($field) . '($' . $field . ')';
				$code[] = ' {';
				$code[] = ' $this->typeErrorHandler(\'' . $this->getFieldType($val) . '\', $' . $field . ');';
				$code[] = ' $this->' . $field . ' = $' . $field . ';';
				$code[] = ' }';
			}
		}

		/**
		 *
		 * @param array $code
		 * @param string $field
		 * @param boolean|integer|float|string|array $val
		 * @return void
		 */
		private function generateGetter(&$code, $field, $val)
		{
			$code[] = '';
			$code[] = ' /**';
			$code[] = ' * Getter for ' . $field;
			$code[] = ' *';
			$code[] = ' * @return ' . $this->getFieldType($val);
			$code[] = ' */';
			$code[] = ' public function get' . ucfirst($field) . '()';
			$code[] = ' {';
			$code[] = ' return $this->' . $field . ';';
			$code[] = ' }';
		}

		/**
		 *
		 * @param boolean|integer|float|string|array $v
		 * @return string
		 */
		private function getFieldType($v)
		{
			if (filter_var($v, FILTER_VALIDATE_INT)) {
				return 'int';
			}

			if (filter_var($v, FILTER_VALIDATE_BOOLEAN)) {
				return 'boolean';
			}

			if (filter_var($v, FILTER_VALIDATE_FLOAT)) {
				return 'float';
			}

			if (is_array($v)) {
				return 'array';
			}

			return 'string';
		}

	}

}