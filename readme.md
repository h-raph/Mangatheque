<div class="statusMenu" aria-hidden="true" role="menu">
                    <ul>
                        <?php foreach ($status as $s): ?>
                            <li role="menuitem" tabindex="0" data-status="<?php echo htmlspecialchars($s); ?>" class="<?= strtolower(str_replace(' ', '-', htmlspecialchars($s))); ?>"><?php echo htmlspecialchars($s); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

<div class="status" data-status="<?php echo htmlspecialchars($s); ?>"></div>