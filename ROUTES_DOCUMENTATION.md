# Web Application Routes Documentation

This document lists all available routes in the application, generated on $(date).

## Public Routes

| Route Name | Method | Path | Description |
|------------|--------|------|-------------|
| `app_home` | GET | `/` | Home page |
| `app_features` | GET | `/features` | Features page |
| `app_register` | ANY | `/register` | User registration |
| `login` | GET\|POST | `/login` | User login |
| `_logout_main` | ANY | `/logout` | User logout |
| `app_offers_index` | GET | `/offers/` | Browse job offers (public) |
| `candidate_offers_list` | GET | `/offers/` | Browse job offers (candidate) |
| `candidate_offers_browse` | GET | `/offers/` | Browse job offers (alternative) |
| `candidate_offer_detail` | GET | `/offers/{slug}` | View job offer details |

## Admin Routes

### Categories Management
| Route Name | Method | Path | Description |
|------------|--------|------|-------------|
| `admin_categories_list` | GET | `/admin/categories` | List all categories |
| `admin_category_create` | GET\|POST | `/admin/categories/create` | Create new category |
| `admin_category_edit` | GET\|POST | `/admin/categories/{id}/edit` | Edit category |
| `admin_category_delete` | POST | `/admin/categories/{id}/delete` | Delete category |
| `admin_category_show` | GET | `/admin/categories/{id}` | View category details |

### Companies Management
| Route Name | Method | Path | Description |
|------------|--------|------|-------------|
| `admin_companies_list` | GET | `/admin/companies` | List all companies |
| `admin_companies_pending` | GET | `/admin/companies/pending` | List pending companies |
| `admin_company_show` | GET | `/admin/companies/{id}` | View company details |
| `admin_company_edit` | GET\|POST | `/admin/companies/{id}/edit` | Edit company |
| `admin_company_approve` | POST | `/admin/companies/{id}/approve` | Approve company |
| `admin_company_reject` | POST | `/admin/companies/{id}/reject` | Reject company |
| `admin_company_delete` | POST | `/admin/companies/{id}/delete` | Delete company |

### Job Offers Management
| Route Name | Method | Path | Description |
|------------|--------|------|-------------|
| `admin_offers_list` | GET | `/admin/offers` | List all job offers |
| `admin_offer_show` | GET | `/admin/offers/{id}` | View job offer details |
| `admin_offer_edit` | GET\|POST | `/admin/offers/{id}/edit` | Edit job offer |
| `admin_offer_toggle` | POST | `/admin/offers/{id}/toggle` | Toggle job offer status |
| `admin_offer_delete` | POST | `/admin/offers/{id}/delete` | Delete job offer |

### Users Management
| Route Name | Method | Path | Description |
|------------|--------|------|-------------|
| `admin_users_list` | GET | `/admin/users` | List all users |
| `admin_user_create` | GET\|POST | `/admin/users/create` | Create new user |
| `admin_user_edit` | GET\|POST | `/admin/users/{id}/edit` | Edit user |
| `admin_user_delete` | POST | `/admin/users/{id}/delete` | Delete user |
| `admin_user_show` | GET | `/admin/users/{id}` | View user details |

### Skills Management
| Route Name | Method | Path | Description |
|------------|--------|------|-------------|
| `admin_skill_index` | GET | `/admin/skills` | List all skills |
| `admin_skill_new` | GET\|POST | `/admin/skills/new` | Create new skill |
| `admin_skill_edit` | GET\|POST | `/admin/skills/{id}/edit` | Edit skill |
| `admin_skill_delete` | POST | `/admin/skills/{id}` | Delete skill |

### Statistics & Logs
| Route Name | Method | Path | Description |
|------------|--------|------|-------------|
| `admin_stats_dashboard` | GET | `/admin/stats` | Statistics dashboard |
| `admin_stats_users` | GET | `/admin/stats/users` | User statistics |
| `admin_stats_companies` | GET | `/admin/stats/companies` | Company statistics |
| `admin_stats_applications` | GET | `/admin/stats/applications` | Application statistics |
| `admin_logs_list` | GET | `/admin/logs` | List admin logs |
| `admin_log_show` | GET | `/admin/logs/{id}` | View admin log details |
| `admin_logs_export` | GET | `/admin/logs/export` | Export admin logs |

## Candidate Routes

### Applications
| Route Name | Method | Path | Description |
|------------|--------|------|-------------|
| `candidate_applications_list` | GET | `/candidate/applications` | List candidate applications |
| `candidate_application_show` | GET | `/candidate/applications/{id}` | View application details |
| `candidate_apply_offer` | GET\|POST | `/candidate/applications/offer/{id}/apply` | Apply to job offer |
| `candidate_application_withdraw` | POST | `/candidate/applications/{id}/withdraw` | Withdraw application |

### Job Offers
| Route Name | Method | Path | Description |
|------------|--------|------|-------------|
| `candidate_offer_save` | POST | `/offers/{id}/save` | Save job offer |
| `candidate_offer_unsave` | POST | `/offers/{id}/unsave` | Unsave job offer |
| `candidate_saved_offers_list` | GET | `/candidate/saved-offers` | List saved offers |
| `candidate_save_offer` | POST | `/candidate/saved-offers/offer/{id}/save` | Save offer (alternative route) |
| `candidate_unsave_offer` | POST | `/candidate/saved-offers/{id}/unsave` | Unsave offer |

### Profile
| Route Name | Method | Path | Description |
|------------|--------|------|-------------|
| `candidate_profile_show` | GET | `/candidate/profile` | View candidate profile |
| `candidate_profile_edit` | GET\|POST | `/candidate/profile/edit` | Edit candidate profile |
| `candidate_cv_download` | GET | `/candidate/profile/cv/download` | Download CV |

## Company Routes

### Applications Management
| Route Name | Method | Path | Description |
|------------|--------|------|-------------|
| `company_applications_list` | GET | `/company/applications` | List company applications |
| `company_application_show` | GET | `/company/applications/{id}` | View application details |
| `company_application_accept` | POST | `/company/applications/{id}/accept` | Accept application |
| `company_application_reject` | POST | `/company/applications/{id}/reject` | Reject application |
| `company_application_cv_download` | GET | `/company/applications/{id}/cv/download` | Download candidate CV |

### Job Offers Management
| Route Name | Method | Path | Description |
|------------|--------|------|-------------|
| `company_offers_list` | GET | `/company/offers` | List company job offers |
| `company_offer_create` | GET\|POST | `/company/offers/new` | Create new job offer |
| `company_offer_edit` | GET\|POST | `/company/offers/{id}/edit` | Edit job offer |
| `company_offer_delete` | POST | `/company/offers/{id}/delete` | Delete job offer |

### Profile
| Route Name | Method | Path | Description |
|------------|--------|------|-------------|
| `company_profile_show` | GET | `/company/profile` | View company profile |
| `app_company_profile_edit` | GET\|POST | `/company/profile/edit` | Edit company profile |
| `company_profile_status` | GET | `/company/profile/status` | View company approval status |

## Debug/Development Routes (Profiler)

These routes are typically only available in development environment:

| Route Name | Method | Path | Description |
|------------|--------|------|-------------|
| `_preview_error` | ANY | `/_error/{code}.{_format}` | Error preview |
| `_wdt` | ANY | `/_wdt/{token}` | Web Debug Toolbar |
| `_wdt_stylesheet` | ANY | `/_wdt/styles` | Web Debug Toolbar styles |
| `_profiler_home` | ANY | `/_profiler/` | Profiler home |
| `_profiler_search` | ANY | `/_profiler/search` | Profiler search |
| `_profiler_search_bar` | ANY | `/_profiler/search_bar` | Profiler search bar |
| `_profiler_phpinfo` | ANY | `/_profiler/phpinfo` | PHP info |
| `_profiler_xdebug` | ANY | `/_profiler/xdebug` | Xdebug info |
| `_profiler_font` | ANY | `/_profiler/font/{fontName}.woff2` | Profiler fonts |
| `_profiler_search_results` | ANY | `/_profiler/{token}/search/results` | Profiler search results |
| `_profiler_open_file` | ANY | `/_profiler/open` | Profiler open file |
| `_profiler` | ANY | `/_profiler/{token}` | Profiler main view |
| `_profiler_router` | ANY | `/_profiler/{token}/router` | Router profiler |
| `_profiler_exception` | ANY | `/_profiler/{token}/exception` | Exception profiler |
| `_profiler_exception_css` | ANY | `/_profiler/{token}/exception.css` | Exception CSS |

## Route Summary

- **Total Routes**: 72
- **Public Routes**: 9
- **Admin Routes**: 29
- **Candidate Routes**: 10
- **Company Routes**: 11
- **Debug/Profiler Routes**: 13

## Notes

- Routes with `{id}` parameter expect an integer ID
- Routes with `{slug}` parameter expect a URL-friendly string identifier
- Routes with `{token}` parameter are used for profiler/debugging purposes
- POST routes typically require CSRF token protection
- Most admin routes require ROLE_ADMIN
- Candidate routes require ROLE_CANDIDATE
- Company routes require ROLE_COMPANY

## Generating This Documentation

To regenerate this documentation, run:

```bash
php bin/console debug:router
```

For more detailed information about a specific route:

```bash
php bin/console debug:router <route_name>
```
