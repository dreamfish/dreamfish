CREATE TABLE project (id BIGINT AUTO_INCREMENT, title VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, deadline DATE NOT NULL, description TEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) ENGINE = INNODB;
CREATE TABLE project_skill (project_id BIGINT, skill_id BIGINT, PRIMARY KEY(project_id, skill_id)) ENGINE = INNODB;
CREATE TABLE skill (id BIGINT, name VARCHAR(255), PRIMARY KEY(id)) ENGINE = INNODB;
ALTER TABLE project_skill ADD CONSTRAINT project_skill_skill_id_skill_id FOREIGN KEY (skill_id) REFERENCES skill(id);
ALTER TABLE project_skill ADD CONSTRAINT project_skill_project_id_project_id FOREIGN KEY (project_id) REFERENCES project(id);
