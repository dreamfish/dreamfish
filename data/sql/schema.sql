CREATE TABLE project (id BIGINT AUTO_INCREMENT, name VARCHAR(255) NOT NULL, request VARCHAR(255) NOT NULL, deadline DATE NOT NULL, description TEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) ENGINE = INNODB;
CREATE TABLE project_skill (project_id BIGINT, skill_id BIGINT, PRIMARY KEY(project_id, skill_id)) ENGINE = INNODB;
CREATE TABLE project_value (project_id BIGINT, value_id BIGINT, PRIMARY KEY(project_id, value_id)) ENGINE = INNODB;
CREATE TABLE skill (id BIGINT, name VARCHAR(255), PRIMARY KEY(id)) ENGINE = INNODB;
CREATE TABLE value (id BIGINT, name VARCHAR(255), type VARCHAR(255), PRIMARY KEY(id)) ENGINE = INNODB;
ALTER TABLE project_skill ADD CONSTRAINT project_skill_skill_id_skill_id FOREIGN KEY (skill_id) REFERENCES skill(id);
ALTER TABLE project_skill ADD CONSTRAINT project_skill_project_id_project_id FOREIGN KEY (project_id) REFERENCES project(id);
ALTER TABLE project_value ADD CONSTRAINT project_value_value_id_value_id FOREIGN KEY (value_id) REFERENCES value(id);
ALTER TABLE project_value ADD CONSTRAINT project_value_project_id_project_id FOREIGN KEY (project_id) REFERENCES project(id);
