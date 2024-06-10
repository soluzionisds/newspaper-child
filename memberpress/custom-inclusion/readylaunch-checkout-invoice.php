<span>
    <?php 
        _ex( '- valido da oggi fino al ', 'ui', 'memberpress' );
        echo date('d/m/Y', $prd->get_expires_at(time()));
    ?>
</span>