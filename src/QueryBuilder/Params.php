<?php


namespace Sre\QueryBuilder;


use function PHPUnit\Framework\isNull;

class Params
{
    public $data = [
        'term' => '',
        'data' => []
    ];
    private $parent;

    public function __construct($parent)
    {
        $this->parent = $parent;
    }

    public function where($col, $mod = null, $val = null)
    {
        return $this->parse_input_clause('AND', $col, $mod, $val);
    }

    public function parse_input_clause($term, $col, $mod, $val)
    {
        if (is_array($col)) {
            foreach ($col as $key => $value) {
                if (is_numeric($key)) {
                    $this->add_data('AND', $value, null, 'IS NOT NULL');
                } else {
                    $this->add_data($term, $key, '=', $value);
                }
            }
            return $this;
        }

        if ($mod === null && $val === null) {
            $val = 'IS NOT NULL';
            $mod = null;
        } else {
            if ($val == null) {
                $val = $mod;
                $mod = '=';
            }
        }

        $this->add_data($term, $col, $mod, $val);
        return $this;
    }

    public function createSQLWhere($data = null, $isSubselect = false)
    {
        if (!$data) {
            $data = $this->data;
        }

        if (!count($data['data'])) {
            return [[], null];
        }

        $allParts = [];
        $localParams = [];

        $PLACEHOLDER = $this->parent->getGrammar()->getPlaceholder();

        foreach ($data['data'] as $index => $spec) {
            if (is_array($spec)) {
                [$col, $term, $val, $join] = $spec;
                if ($index > 0) {
                    $allParts[] = " $join ";
                }
                if ($term === 'IN' || $term === 'NOT IN') {
                    if (is_string($val)) {
                        $parts = array_map('trim', explode(',', $val));
                        $phText = implode(',', array_fill(0, count($parts), $PLACEHOLDER));
                        array_push($localParams, ...$parts);
                        $allParts[] = "$col $term ($phText)";
                        continue;
                    }

                    if (is_array($val)) {
                        array_push($localParams, ...$val);
                        $phText = implode(',', array_fill(0, count($val), $PLACEHOLDER));
                        $allParts[] = "$col $term ($phText)";
                        continue;
                    }

                    if (get_class($val) == QueryBuilder::class) {
                        // subquery
                        [$sql, $params] = $val->createSelect();
                        array_push($localParams, ...$params);
                        $allParts[] = "$col $term ($sql)";
                        continue;
                    }
                }

                if ($term !== null) {
                    if ($val instanceof Raw) {
                        $allParts[] = "$col $term " . $val->getValue();
                        continue;
                    }

                    $localParams[] = $val;
                    $allParts[] = "$col$term$PLACEHOLDER";
                    continue;
                }

                if ($val === 'IS NULL' || $val === 'IS NOT NULL') {
                    $allParts[] = "$col $val";
                    continue;
                }
            }

            /** @var Params $spec */
            if (get_class($this) == get_class($spec)) {
                [$sqlParams, $sqlText] = $spec->createSQLWhere(null, true);
                $allParts[] = " {$spec->data['term']} ($sqlText)";
                array_push($localParams, ...$sqlParams);
            }
        }

        $w = $isSubselect ? '' : 'WHERE ';
        return [$localParams, $w . notNullAdder::create($allParts, '')];
        return [$localParams, $w . implode('', $allParts)];
    }


    private function add_data($term, $col, $mod, $val)
    {
        $this->data['data'][] = [$col, $mod, $val, $term];
    }
}