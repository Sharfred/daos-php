const bcrypt = require('bcryptjs');
console.log(bcrypt.compareSync('admin123', '$2y$10$w6z/wAOTZq0R/7a6R2u9cObIeK2o.ZlKxj3w3o/hD0q.K6S.1u0K2'));
