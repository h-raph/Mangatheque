<form method="GET"> 
                    <label for="genre">Genres</label>
                    <select name="genre" id="genre" onchange="this.form.submit()">
                        <?php if (!empty($genresArray)): ?>
                            <option value="Tout">-- Tout --</option>
                            <?php foreach ($genresArray as $g): ?>
                                <?php $select = $selectGenre === $g ? 'selected' : '' ?>
                                <option value="<?php echo $g; ?>" <?= $select ?>>
                                    <?php echo $g; ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">Aucun genre</option>
                        <?php endif; ?>
                    </select>