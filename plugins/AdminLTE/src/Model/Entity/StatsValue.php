<?php
namespace AdminLTE\Model\Entity;

use Cake\ORM\Entity;

/**
 * StatsValue Entity
 *
 * @property int $id
 * @property int $stats_config_id
 * @property string $interval_total
 * @property float $total_total
 * @property int $count
 * @property float $average
 * @property float $average_growth_rate
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\StatsConfig $stats_config
 */
class StatsValue extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];
}
