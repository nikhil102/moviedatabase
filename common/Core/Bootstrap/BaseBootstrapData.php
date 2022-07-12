<?php namespace Common\Core\Bootstrap;

use App;
use Common\Admin\Appearance\Themes\CssTheme;
use Common\Auth\Roles\Role;
use Common\Localizations\Localization;
use Common\Localizations\LocalizationsRepository;
use Common\Settings\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class BaseBootstrapData implements BootstrapData
{
    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Localization
     */
    protected $localizationRepository;

    /**
     * @var Role
     */
    protected $role;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param Settings $settings
     * @param Request $request
     * @param Role $role
     * @param LocalizationsRepository $localizationsRepository
     */
    public function __construct(
        Settings $settings,
        Request $request,
        Role $role,
        LocalizationsRepository $localizationsRepository
    )
    {
        $this->role = $role;
        $this->request = $request;
        $this->settings = $settings;
        $this->localizationRepository = $localizationsRepository;
    }

    public function getEncoded()
    {
        // prevent double printing themes to save some bytes
        $data = config('app.env') === 'production' ?
            Arr::except($this->data, 'themes') :
            $this->data;

        if ($this->data['user']) {
            $this->data['user'] = $this->data['user']->toArray();
        }

        return base64_encode(json_encode($data));
    }

    public function get($key = null)
    {
        return $key ? Arr::get($this->data, $key) : $this->data;
    }

    public function init()
    {
        $this->data['settings'] = $this->settings->all();
        $this->data['settings']['base_url'] = url('');
        $this->data['settings']['version'] = config('common.site.version');
        $this->data['user'] = $this->getCurrentUser();
        $this->data['guests_role'] = $this->role->where('guests', 1)->with('permissions')->first();
        $this->data['i18n'] = $this->getLocalizationsData() ?: null;
        $this->data['themes'] = $this->getThemes();

        if (config('common.site.notifications_integrated') && $this->data['user']) {
            $this->data['user']->unread_notifications_count = $this->data['user']
                ->unreadNotifications()->count();
        }

        // TODO: remove this after all apps are refactored
        if ($namespace = config('common.site.extra_bootstrap_data')) {
            $this->data = App::make($namespace)->get($this->data);
        }

        return $this;
    }

    protected function getThemes()
    {
        $themes = app(CssTheme::class)
            ->where('default_dark', true)
            ->orWhere('default_light', true)
            ->get();

        $defaultDark = new CssTheme(['name' => 'dark', 'is_dark' => true, 'colors' => config('common.themes.dark')]);
        $defaultLight = new CssTheme(['name' => 'light', 'is_light' => true, 'colors' => config('common.themes.light')]);

        return [
            'dark' => $themes->where('default_dark', true)->first() ?: $defaultDark,
            'light' => $themes->where('default_light', true)->first() ?: $defaultLight,
        ];
    }

    /**
     * Load current user and his roles.
     */
    protected function getCurrentUser()
    {
        $user = $this->request->user();
        if ($user) {
            // load user subscriptions, if billing is enabled
            if (app(Settings::class)->get('billing.enable') && ! $user->relationLoaded('subscriptions')) {
                $user->load('subscriptions.plan');
            }

            // load user roles, if not already loaded
            if ( ! $user->relationLoaded('roles')) {
                $user->load('roles');
            }

            if ( ! $user->relationLoaded('permissions')) {
                $user->loadPermissions();
            }
        }

        return $user;
    }

    /**
     * Get currently selected i18n language.
     *
     * @return Localization
     */
    protected function getLocalizationsData()
    {
        if ( ! $this->settings->get('i18n.enable')) return null;

        //get user selected or default language
        $userLang = $this->request->user() ? $this->request->user()->language : null;

        if ( ! $userLang) {
            $userLang = config('app.locale');
        }

        if ($userLang) {
            return $this->localizationRepository->getByName($userLang);
        }
    }
}
