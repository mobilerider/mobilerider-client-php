<?php

namespace Mr\Api\Query;

use Mr\Exception\InvalidFiltersException;

function Q(array $filters, array $exclude = array(), array $fields = array())
{
    return new QuerySet($filters, $exclude, $fields);
}

/**
 * Class QuerySet
 * @package Mr\Api\Query
 *
 * @method QuerySet exact($field, $value)
 * @method QuerySet contains($field, $value)
 * @method QuerySet in($field, $value)
 * @method QuerySet startsWidth($field, $value)
 * @method QuerySet endsWidth($field, $value)
 * @method QuerySet isNull($field, $value)
 * @method QuerySet iExact($field, $value)
 * @method QuerySet iStartsWith($field, $value)
 * @method QuerySet iEndsWith($field, $value)
 * @method QuerySet gt($field, $value)
 * @method QuerySet gte($field, $value)
 * @method QuerySet lt($field, $value)
 * @method QuerySet lte($field, $value)
 * @method QuerySet regex($field, $value)
 * @method QuerySet range($field, $value)
 * @method QuerySet year($field, $value)
 * @method QuerySet month($field, $value)
 * @method QuerySet day($field, $value)
 * @method QuerySet hour($field, $value)
 * @method QuerySet minute($field, $value)
 * @method QuerySet second($field, $value)
 * @method QuerySet weekDay($field, $value)
 *
 */
class QuerySet
{
    const CONDITION_SEPARATOR = '__';

    const OPERATOR_OR = 'or';
    const OPERATOR_AND = 'and';
    const OPERATOR_NOT = 'not';

    protected $_fields = array();
    protected $_filters = array();

    protected $_allowedOperators = array(
        self::OPERATOR_AND, self::OPERATOR_OR, self::OPERATOR_NOT
    );

    protected $_allowedFilters = array(
        'exact', 'contains', 'in', 'startswith', 'endswith', 'isnull', 'iexact', 'istartswith', 'iendswith',
        'gt', 'gte', 'lt', 'lte', 'regex', 'range', 'year', 'month', 'day', 'hour', 'minute', 'second', 'weekday'
    );

    public function __construct(array $filters = array(), array $fields = array())
    {
        if (!empty($filters)) {
            $this->filter($filters);
        }

        if (!empty($fields)) {
            $this->select($fields);
        }
    }

    public function select($field)
    {
        $fields = array();

        if (func_num_args() > 1) {
            $fields = array_merge($fields, $field);
        } else if (is_array($field)) {
            if (isset($field[0])) {
                $fields = array_merge($fields, $field);
            } else {
                $fields[] = $field;
            }
        } else if (is_string($field)) {
            $fields[] = $field;
        } else {
            throw new \Exception('Invalid select field');
        }

        foreach ($fields as $field) {
            $this->validateField($field);
        }

        $this->_fields = array_merge($this->_fields, $fields);

        return $this;
    }

    protected function validateField($field)
    {
        if (!is_string($field)) {
            throw new \Exception('Invalid select field');
        }

        return true;
    }
    
    protected function validateFilter($filter)
    {
        if (!in_array($filter, $this->_allowedFilters)) {
            throw new InvalidFiltersException(array($filter), $this->_allowedFilters);
        }
        
        return true;
    }

    protected function parseFilters($field, $filter = '', $value = '')
    {
        $filters = array();

        if (is_array($field)) {
            foreach ($field as $filter => $value) {
                if (is_numeric($filter)) {
                    if (is_string($value)) {
                        $filters[] = $this->createPredicateOperator($value);
                    } else if (is_array($value)) {
                        // Recursion to step inside the whole filter tree
                        if (count($value) == 1) {
                            $filters = array_merge($filters, $this->parseFilters($value));
                        } else if (count($value) > 1) {
                            $filters[] = $this->parseFilters($value);
                        } else {
                            throw new \Exception('Empty filter or set of filters');
                        }
                    }
                } else if (is_string($filter)) {
                    if (is_array($value)) {
                        $filters[] = $this->createPredicateOperator($filter);
                        // Recursion to step inside the whole filter tree
                        if (count($value) == 1) {
                            $filters = array_merge($filters, $this->parseFilters($value));
                        } else if (count($value) > 1) {
                            $filters[] = $this->parseFilters($value);
                        } else {
                            throw new \Exception('Empty filter or set of filters');
                        }
                    } else {
                        $parts = explode(self::CONDITION_SEPARATOR, $filter);

                        if (count($parts) < 2) {
                            throw new \Exception('Left side needs to include at least a field and a filter');
                        }

                        $filter = array_pop($parts);

                        $filters[] = $this->createFilter(implode('__', $parts), $filter, $value);
                    }
                } else {
                    throw new \Exception('Invalid filter left side');
                }
            }
        } else {
            $filters[] = $this->createFilter($field, $filter, $value);
        }

        return $filters;
    }

    public function filter($field, $filter = '', $value = '')
    {
        if (empty($field)) {
            throw new \Exception('Invalid select field');
        }
        $newFilters =  $this->parseFilters($field, $filter, $value);

        if (empty($this->_filters)) {
            $this->_filters = $this->parseFilters($field, $filter, $value);
        } else {
            foreach ($newFilters as $filter) {
                $this->_filters[] = $filter;
            }
        }

        return $this;
    }

    protected function createPredicateOperator($op)
    {
        if (!is_string($op) || !in_array($op, $this->_allowedOperators)) {
            throw new InvalidFiltersException(array($op), $this->_allowedOperators);
        }

        return $op;
    }

    protected function createFilter($field, $filter, $value)
    {
        $filter = strtolower($filter);

        $this->validateField($field);
        $this->validateFilter($filter);

        return array($field . '__' . $filter => $value);
    }

    public function exclude($field, $filter, $value)
    {
        if (!empty($field)) {
            throw new \Exception('Invalid select field');
        }

        $this->_filters[] = self::OPERATOR_NOT;
        $this->_filters = array($this->_filters, $this->parseFilters($field, $filter, $value));

        return $this;
    }

    public function __call($name, $arguments)
    {
        if (empty($arguments)) {
            throw new \Exception('Filters require at least one argument');
        }

        if (strpos($name, '__')) {
            $this->filter(array($name => $arguments[0]));
        } else {
            if (count($arguments) < 2) {
                throw new \Exception('Field and value are required when executing calling filter just by name');
            }

            $this->filter($arguments[0], $name, $arguments[1]);
        }

        return $this;
    }

    public function toArray()
    {
        $a = array();

        if (!empty($this->_fields)) {
            $a['fields'] = $this->_fields;
        }

        if (!empty($this->_filters)) {
            $a['filters'] = $this->_filters;
        }

        return $a;
    }

    public function toJSON()
    {
        return json_encode($this->toArray());
    }
} 