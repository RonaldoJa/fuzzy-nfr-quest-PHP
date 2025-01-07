```sql
TABLE users {
  id INT [PK, increment, not null]
  first_name VARCHAR(50) [not null]
  last_name VARCHAR(50) [not null]
  email VARCHAR(100) [not null, unique, note: 'This field must be unique for each user']
  password VARCHAR(255) [not null]
  role_id ENUM('student', 'admin') [default: 'student']
  created_at TIMESTAMP [default: 'CURRENT_TIMESTAMP']
  updated_at TIMESTAMP [default: 'CURRENT_TIMESTAMP', note: 'Updates on change']
}

TABLE rooms {
  id INT [PK, increment, not null]
  room_name VARCHAR(50) [not null, unique]
  room_code VARCHAR(255) [not null]
  user_id INT [not null, ref: > users.id]
  created_at TIMESTAMP [default: 'CURRENT_TIMESTAMP']
  max_attempts INT [not null, default: -1, note: '-1 to indicate unlimited']
}

TABLE tried {
  id INT [PK, increment, not null]
  user_id INT [not null, ref: > users.id]
  room_id INT [not null, ref: > rooms.id]
  totalreq INT [not null]
  movements INT [not null]
  score INT [not null]
  status ENUM('completed', 'abandoned') [not null]
  time TIME [not null, note: 'time in the format: 00:01:35']
}
```