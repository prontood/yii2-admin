<?php

namespace mdm\admin;

use Yii;
use mdm\admin\components\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\db\Connection;

/**
 * Description of Module
 *
 * @author MDMunir
 */
class Module extends \yii\base\Module implements \yii\base\BootstrapInterface
{
    public $defaultRoute = 'assigment';

    /**
     *
     * @var string
     * Position of menu. Avaliable value are left, top, right and false.
     * Default to left
     */
    public $positionMenu = 'left';

    /**
     *
     * @var array
     */
    public $allowActions = [];

    /**
     *
     * @var array
     */
    public $items = [];

    /**
     *
     * @var array
     */
    public $menus;

    /**
     *
     * @var Connection
     */
    public $db = 'db';

    /**
     *
     * @var string
     * Customize "id" field
     */
    public $idField = 'id';

    /**
     *
     * @var string
     * Customize "username" field
     */
    public $usernameField = 'username';

    public function init()
    {
        parent::init();
        $this->db = $this->db instanceof Connection ? $this->db : Yii::$app->get($this->db, false);
    }

    /**
     *
     * @param \yii\web\Application $app
     */
    public function bootstrap($app)
    {
        $app->attachBehavior(AccessControl::className(), new AccessControl([
                    'allowActions' => $this->allowActions
                ]));
    }

    protected function getCoreItems()
    {

        return [
            'assigment' => [
                'class' => 'mdm\admin\items\AssigmentController'
            ],
            'role' => [
                'class' => 'mdm\admin\items\RoleController'
            ],
            'permission' => [
                'class' => 'mdm\admin\items\PermissionController'
            ],
            'route' => [
                'class' => 'mdm\admin\items\RouteController'
            ],
            'rule' => [
                'class' => 'mdm\admin\items\RuleController'
            ],
            'menu' => [
                'class' => 'mdm\admin\items\MenuController',
                'visible' => $this->db !== null && $this->db->schema->getTableSchema('{{%menu}}') !== null
            ],
        ];
    }

    private function normalizeController()
    {
        $controllers = [];
        $this->menus = [];
        $mid = '/' . $this->getUniqueId() . '/';
        $items = ArrayHelper::merge($this->getCoreItems(), $this->items);
        foreach ($items as $id => $config) {
            $label = Inflector::humanize($id);
            $visible = true;
            if (is_array($config)) {
                $label = ArrayHelper::remove($config, 'label', $label);
                $visible = ArrayHelper::remove($config, 'visible', true);
            }
            if ($visible) {
                $this->menus[] = ['label' => $label, 'url' => [$mid . $id]];
                $controllers[$id] = $config;
            }
        }
        $this->controllerMap = ArrayHelper::merge($this->controllerMap, $controllers);
    }

    public function createController($route)
    {
        $this->normalizeController();
        return parent::createController($route);
    }
}
