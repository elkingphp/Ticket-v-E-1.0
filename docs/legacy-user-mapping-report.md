# Legacy User Mapping Report (MRI)
Date: 2026-02-14

## Initial Polymorphic Types Found
- **notifications**: 0 records (0 unique types)
- **model_has_roles**: 4 records
  - Current Types: `['user']`
- **personal_access_tokens**: 0 records
- **activity_log**: N/A (table not found)

## Target Mapping
- All occurrences of `Modules\Core\Domain\Models\User` -> `user`
- All occurrences of `Modules\Users\Domain\Models\User` -> `user`

## Risk Assessment
- **Data Volume**: Very Low (Safe for single-transaction update).
- **Blocking Risk**: Low.
