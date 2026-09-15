<?= $this->extend('webadmin/layout') ?>

<?= $this->section('content') ?>
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Live Chat</h2>
            </div>
        </div>
    </div>
</div>
<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="card-body">
                <div class="empty py-5">
                    <div class="empty-icon"><i class="ti ti-lock" style="font-size: 3rem;"></i></div>
                    <p class="empty-title">Private conversation</p>
                    <p class="empty-subtitle text-secondary">
                        Conversation #<?= (int) $conversationId ?> bersifat private dan Anda bukan anggotanya.
                    </p>
                    <div class="empty-action">
                        <a href="<?= base_url('chat') ?>" class="btn btn-primary">
                            <i class="ti ti-arrow-left me-2"></i>Back to Live Chat
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
