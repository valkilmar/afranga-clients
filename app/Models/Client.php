<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Phone;


class Client extends Model
{
    use HasFactory;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'personal_no',
        'card_no'
    ];


    protected $fillPhones = [];

    /**
     * Returns a client's phone numbers.
     */
    public function phones()
    {
        return $this->hasMany(Phone::class)->orderBy('created_at', 'desc');
    }


    public function fill(array $attributes)
    {
        $this->fillPhones = [];

        if (isset($attributes['phones'])) {

            // Sanitize phones
            array_map(function($number) {
                if (is_string($number)) {
                    $number = preg_replace('/\D/', '', $number);

                    if (!empty($number) && !in_array($number, $this->fillPhones)) {
                        $this->fillPhones[] = $number;
                    }
                }
                
            }, $attributes['phones']);

            $phones = $this->phones;

            $phones->each(function (Phone $phone, int $key) use(&$filled) {
                if (array_key_exists($key, $this->fillPhones)) {
                    $phone->fill(['number' => $this->fillPhones[$key]]);
                }
            });       
        }

        return parent::fill($attributes);
    }


    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        $res = parent::toArray();
        return array_merge($res, [
            'fillPhones' => $this->fillPhones
        ]);
    }


    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArrayForExport()
    {

        $phoneNumbersOnly = $this->phones()->pluck('number')->all();
 
        return [
            'name' => $this->name,
            'personal_no' => $this->personal_no,
            'card_no' => $this->card_no,
            'phones' => implode(', ', $phoneNumbersOnly)
        ];
    }


    /**
     * Determine if the model or any of the given attribute(s) have been modified.
     *
     * @param  array|string|null  $attributes
     * @return bool
     */
    public function isDirty($attributes = null)
    {

        if (parent::isDirty($this->fillable)) {
            return true;
        }

        $phones = $this->phones;

        if (count($this->fillPhones) !== $phones->count()) {
            return true;
        }

        $foundDirtyPhone = false;

        $phones->each(function (Phone $phone, int $key) use(&$foundDirtyPhone) {
            if ($phone->isDirty()) {
                $foundDirtyPhone = true;
                return false;
            }
        });

        return $foundDirtyPhone;
    }



    /**
     * Save the model and all of its relationships.
     *
     * @return bool
     */
    public function push()
    {
        $res = parent::push();

        if ($res) {

            if (empty($this->fillPhones)) {
                $this->phones()->delete();
            } else {

                $phones = $this->phones;

                // Add new phones
                foreach($this->fillPhones as $number) {
                    if (!$phones->contains('number', $number)) {
                        $phones->push(Phone::create([
                            'client_id' => $this->id,
                            'number' => $number
                        ]));
                    }
                }

                // Remove redundant phones
                $phones->each(function (Phone $phone, int $key) {
                    if (!in_array($phone->number, $this->fillPhones)) {
                        $phone->delete();
                    }
                });
            } 
        }

        $this->refresh();

        return $res;
    }
}
