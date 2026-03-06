# Message

## Method

```php
$group->addMessage(string $label, string $message, array $args = [])
```

## Example

```php
$group->addMessage('Help', 'This section controls homepage hero content.', [
    'esc_html' => 0,
    'new_lines' => 'wpautop',
]);
```

## Notes

- The internal field name is generated from the label and ends with `_message`.

## Common ACF Options

- `message`
- `new_lines`
- `esc_html`
