<?php

namespace ORM\Helpers;

use ORM\Orm;

use ORM\Constants\CascadeTypes;

use ORM\Mappers\Column;
use ORM\Mappers\Join;
use ORM\Mappers\JoinTable;
use ORM\Mappers\Table;

class Annotation
{

	private $orm;

	private $class;

	private $table;

	private $resolver;

	private $reflect;

	public function __construct(String $class)
	{
		$this->class = $class;
		$this->orm = Orm::getInstance();
		$this->table = new Table($class);
		$this->resolver = new ExpressionResolver();
		$this->reflect = new \ReflectionClass($class);
	}

	public function mapper() : Table
	{
		$class = $this->resolveClass();

		foreach($this->reflect->getProperties() as $property) {
			$this->resolveProperty($property);
		}

		return $this->table;
	}

	private function resolveClass()
	{
		if (!($class = $this->resolver->get('orm', $this->reflect->getDocComment(), true))) {
			throw new \Exception("A classe \"$this->class\" não está devidamente anotada");
		}

		if (!$this->resolver->get('entity', $class)) {
			throw new \Exception("Está faltando a anotação \"Entity\" na classe \"$this->class\"");
		}

		$table = $this->resolver->get('table', $class);
		$name = $this->resolver->get('name', $table);

		if (!$table || !$name) {
			$c = explode('\\', $this->class);
			$name = strtolower(end($c));
		}

		$this->table->setName($name);

		if ($schema = $this->resolver->get('schema', $table)) {
			$this->table->setSchema($schema);
		}

		if ($mutable = $this->resolver->get('mutable', $table)) {
			$this->table->setMutable($mutable === 'true');
		}
	}

	private function resolveProperty(\ReflectionProperty $property)
	{
		$prop = $this->resolver->get('orm', $property->getDocComment(), true);

		if ($this->resolver->get('transient', $prop)) return;

		if ($this->resolver->get('hasOne', $prop)) {
			$this->resolveJoin($property, 'hasOne');
		} elseif ($this->resolver->get('hasMany', $prop)) {
			$this->resolveJoin($property, 'hasMany');
		} elseif ($this->resolver->get('manyToMany', $prop)) {
			$this->resolveJoin($property, 'manyToMany');
		} elseif ($this->resolver->get('belongsTo', $prop)) {
			$this->resolveJoin($property, 'belongsTo');
		} else {
			$this->resolveColumn($property);
		}
	}

	private function resolveJoin(\ReflectionProperty $property, String $type)
	{
		$join = new Join();
		$prop = $this->resolver->get('orm', $property->getDocComment(), true);

		if ($has = $this->resolver->get($type, $prop)) {
			$reference = $this->resolver->get('className', $has);

			if (!$reference) {
				throw new \Exception('É obrigatório informar a classe de referência');
			}

			if (!class_exists($reference)) {
				throw new \Exception("A classe \"$reference\" não existe");
			}

			$join->setReference($reference);
		}

		$join->setProperty($property->getName());
		$join->setType($type);

		if ($cascade = $this->resolver->get('cascade', $has)) {
			$cascade = preg_split("/,\s?/i", $cascade);

			if(in_array('ALL', $cascade)) {
				$cascade = CascadeTypes::TYPES;
			}

			foreach ($cascade as $c) {
				if (!in_array($c, CascadeTypes::TYPES)) {
					throw new \Exception('Cascade type "' . $c . '" does not exists');
				}
			}

			$join->setCascade($cascade);
		}

		if ($optional = $this->resolver->get('optional', $has)) {
			$join->setOptional($optional === 'true');
		}

		if ($type === 'manyToMany') {
			if ($mappedBy = $this->resolver->get('mappedBy', $has)) {
				$join->setMappedBy($mappedBy);
			} else {
				$table = new JoinTable();
				$reference = $this->orm->getTable($join->getReference());

				if ($joinTable = $this->resolver->get('joinTable', $prop)) {
					if ($name = $this->resolver->get('tableName', $joinTable)) {
						$table->setName($name);
					}

					if ($schema = $this->resolver->get('schema', $joinTable)) {
						$table->setSchema($schema);
					}

					if ($joinColumn = $this->resolver->get('join', $joinTable)) {
						$name = $this->resolver->get('name', $joinColumn);
						$table->setJoinName($name);
					}

					if ($inverseJoinColumn = $this->resolver->get('inverse', $joinTable)) {
						$name = $this->resolver->get('name', $inverseJoinColumn);
						$table->setInverseName($name);
					}
				}

				if (empty($table->getName())) {
					$name = $this->table->getName() . '_' . $reference->getName();
					$table->setName($name);
				}

				if (empty($table->getJoinName())) {
					$joinName = $this->table->getName() . '_id';
					$table->setJoinName($joinName);
				}

				if (empty($table->getInverseName())) {
					$inverseName = $reference->getName() . '_id';
					$table->setInverseName($inverseName);
				}

				$join->setJoinTable($table);
			}
		} elseif ($type === 'belongsTo') {
			if ($column = $this->resolver->get('joinColumn', $prop)) {
				$name = $this->resolver->get('name', $column);
				$join->setName($name);
			}

			if (empty($join->getName())) {
				$name = $property->getName() . '_id';
				$join->setName($name);
			}
		}

		$this->table->addJoin($join);
	}

	private function resolveColumn(\ReflectionProperty $property)
	{
		$column = new Column();
		$prop = $this->resolver->get('orm', $property->getDocComment(), true);

		if ($id = $this->resolver->get('id', $prop)) {
			$column->setId(!!$id);
		}

		if ($generated = $this->resolver->get('generated', $prop)) {
			$column->setGenerated(!!$generated);
		}

		if ($_column = $this->resolver->get('column', $prop)) {
			if ($name = $this->resolver->get('name', $_column)) {
				$column->setName($name);
			} else {
				$column->setName($property->getName());
			}

			if ($type = $this->resolver->get('type', $_column)) {
				$column->setType($type);
			}

			if ($length = $this->resolver->get('length', $_column)) {
				$column->setLength((int) $length);
			}

			if ($scale = $this->resolver->get('scale', $_column)) {
				$column->setScale((int) $scale);
			}

			if ($precision = $this->resolver->get('precision', $_column)) {
				$column->setPrecision((int) $precision);
			}

			if ($unique = $this->resolver->get('unique', $_column)) {
				$unique = !is_null($unique) && $unique === 'true' && !$id;
				$column->setUnique($unique);
			}

			if ($nullable = $this->resolver->get('nullable', $_column)) {
				$column->setNullable($nullable === 'true' || is_null($nullable));
			}

			if ($column->isId() || $column->isUnique()) {
				$column->setNullable(false);
			}
		} else {
			$column->setName($property->getName());
		}

		$column->setProperty($property->getName());
		$this->table->addColumn($column);
	}

}
