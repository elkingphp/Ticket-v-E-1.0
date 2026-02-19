<div class="empty-notification-elem text-center py-5">
    <div class="avatar-lg dflex align-items-center justify-content-center mx-auto mb-4 bg-light-subtle rounded-circle">
        <i class="ri-notification-off-line text-muted display-4"></i>
    </div>
    <div class="px-4">
        <h6 class="fs-16 fw-semibold text-dark">{{ app()->getLocale() == 'ar' ? 'لا توجد تنبيهات' : 'No notifications yet' }}</h6>
        <p class="text-muted mb-0">{{ app()->getLocale() == 'ar' ? 'سنخطرك هنا عند وجود أي تحديثات جديدة.' : "We'll notify you when something new arrives." }}</p>
    </div>
</div>
