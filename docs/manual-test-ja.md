# 手動テストメモ

## ローカル起動

- [ ] `cp config/config.example.php config/config.php`
- [ ] `php scripts/create_password_hash.php "your-password"`
- [ ] `php scripts/init_db.php`
- [ ] `php -S localhost:8000 -t public`

## 管理画面

- [ ] `/admin/` が表示される
- [ ] 間違ったログイン情報が拒否される
- [ ] 正しいログイン情報で入れる
- [ ] series を作成できる
- [ ] episode を作成できる
- [ ] 画像を3枚以上 upload できる
- [ ] spacer block を追加できる
- [ ] text block を追加できる
- [ ] block の上下移動ができる

## 公開側

- [ ] draft の series / episode が表示されない
- [ ] published の series / episode が表示される
- [ ] reader で画像が縦に並ぶ
- [ ] spacer / text block が表示される
- [ ] 前後 episode link が動く

## デプロイ前確認

- [ ] `config/config.php` を commit していない
- [ ] `data/app.sqlite` を commit していない
- [ ] production uploads を commit していない
- [ ] `public/uploads/` に書き込み権限がある
- [ ] `data/` に書き込み権限がある
- [ ] PHP の `upload_max_filesize` と `post_max_size` が十分大きい
