SELECT
	u.personaname,
    CONCAT(u.wallet_balance, '$'),
	u.likes_balance,
    likes.likesCount,
    u.wallet_total_refilled,
FROM user u,
LATERAL(
	SELECT sum(amount) AS likesCount
    FROM analytics a
    WHERE a.user_id = u.id AND a.object = 'boosterpack'
) likes
WHERE u.id = 1;


SELECT
	HOUR(a.time_created),
	sum(b.price) AS 'boosterSum',
    CONCAT(sum(a.amount), '$') AS 'profit'
	CONCAT('Boosterpack #', a.object_id),
FROM analytics a
LEFT JOIN boosterpack b ON b.id = a.object_id
WHERE a.object = 'boosterpack' AND a.time_created > NOW() - INTERVAL 30 DAY

GROUP BY object_id, HOUR(a.time_created);


SELECT
	u.personaname,
	HOUR(a.time_created),
	sum(b.price),
	CONCAT('Boosterpack #', a.object_id),
    CONCAT(u.wallet_balance, '$'),
    u.likes_balance,
    CONCAT(sum(a.amount), '$'),
    u.wallet_total_refilled,
    likes.likesCount
FROM analytics a
LEFT JOIN user u ON u.id = a.user_id,
LATERAL(
	SELECT sum(amount)
    FROM analytics a
    WHERE a.user_id = u.id AND a.object = 'boosterpack'
) likes
LEFT JOIN boosterpack b ON b.id = a.object_id
WHERE a.object = 'boosterpack' AND a.time_created > NOW() - INTERVAL 30 DAY

GROUP BY object_id, HOUR(a.time_created), u.likes_balance, likes.likesCount, u.wallet_total_refilled, u.personaname, u.wallet_balance;