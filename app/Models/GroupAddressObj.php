<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupAddressObj extends Model
{
    use HasFactory;

    // Имя таблицы в БД
    protected $table = 'group_address_objs';


    // Поля, доступные для массового заполнения
    protected $fillable = [
        'address',
        'latitude',
        'longitude',
        'subj_id'
    ];

    // Типы данных атрибутов
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Связь: группа адресов имеет много субъектов (AddressSubj)
     * Внешний ключ в таблице address_subjs — group_id
     */
    public function subjects()
    {
        return $this->hasMany(AddressSubj::class, 'group_id');
    }

    /**
     * Связь: группа адресов принадлежит одному субъекту (Subj)
     * Внешний ключ в текущей таблице — subj_id
     */
    public function subj()
    {
        return $this->belongsTo(Subj::class, 'subj_id');
    }

    /**
     * Связь: группа адресов связана с одним объектом (Obj)
     * Предполагаем, что связь через поле obj_id в текущей таблице
     * Если связь через другую таблицу, нужно скорректировать
     */
    public function obj()
    {
        // Если связь «один‑к‑одному» через obj_id в group_address_objs
        return $this->belongsTo(Obj::class, 'obj_id');
    }

    /**
     * Вспомогательный метод для получения всех объектов через субъектов
     * Используется, если нужно получить все объекты, связанные с субъектами группы
     */
    public function objsThroughSubjects()
    {
        return $this->hasManyThrough(
            Obj::class,
            AddressSubj::class,
            'group_id',    // Внешний ключ в AddressSubj
            'id',        // Внешний ключ в Obj
            'id',        // Локальный ключ в GroupAddressObj
            'obj_id'     // Локальный ключ в AddressSubj
        );
    }
}
