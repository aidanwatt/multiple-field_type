<?php

namespace Anomaly\MultipleFieldType\Handler;

use Illuminate\Support\Str;
use Anomaly\MultipleFieldType\MultipleFieldType;

/**
 * Class Related
 *
 * @link   http://pyrocms.com/
 * @author PyroCMS, Inc. <support@pyrocms.com>
 * @author Ryan Thompson <ryan@pyrocms.com>
 */
class Related
{

    /**
     * Handle the options.
     *
     * @param  MultipleFieldType $fieldType
     */
    public function handle(MultipleFieldType $fieldType)
    {
        $model = $fieldType->getRelatedModel();

        $query   = $model->newQuery();
        $results = $query->get();

        $parsable = Str::contains($fieldType->config('title_name', $model->stream()->title_column), ['{', '::']);

        try {

            /**
             * Try and use a non-parsing pattern.
             */
            if (!$parsable) {
                $fieldType->setOptions(
                    $options = array_combine(
                        $results->map(
                            function ($item) use ($fieldType, $model) {
                                return data_get($item, $fieldType->config('key_name', $model->getKeyName()));
                            }
                        )->all(),
                        $results->map(
                            function ($item) use ($fieldType, $model) {
                                return valuate($fieldType->config('title_name', $model->stream()->title_column), $item);
                            }
                        )->all()
                    )
                );
            }

            /**
             * Try and use a parsing pattern.
             */
            if ($parsable) {
                $fieldType->setOptions(
                    array_combine(
                        $results->map(
                            function ($item) use ($fieldType, $model) {
                                return data_get($item, $fieldType->config('key_name', $model->getKeyName()));
                            }
                        )->all(),
                        $results->map(
                            function ($item) use ($fieldType, $model) {
                                return valuate($fieldType->config('title_name', $model->stream()->title_column), $item);
                            }
                        )->all()
                    )
                );
            }
        } catch (\Exception $e) {
            $fieldType->setOptions(
                $results->pluck(
                    $model->getTitleName(),
                    $model->getKeyName()
                )->all()
            );
        }
    }
}
