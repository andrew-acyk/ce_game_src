<?php
?>
<?php $result = showAllTransactions($_POST['account_id']); ?>

<table>
  <tr>
<?php foreach ($result as $row): array_map('htmlentities', $row); ?>
  <tr>
    <td><?php echo implode('</td><td>', $row); ?></td>
  </tr>
<?php endforeach; ?>
</table>
